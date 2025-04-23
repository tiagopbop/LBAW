DROP SCHEMA IF EXISTS lbaw24146 CASCADE;
CREATE SCHEMA IF NOT EXISTS lbaw24146;
SET search_path TO lbaw24146;


DROP FUNCTION IF EXISTS update_ts_vector_project CASCADE;
DROP TRIGGER IF EXISTS update_task_and_project_timestamp_trigger ON task;
DROP FUNCTION IF EXISTS update_task_and_project_timestamp;
DROP TRIGGER IF EXISTS TRIGGER_TASK_ASSIGN_notifY ON user_task;
DROP FUNCTION IF EXISTS notify_task_assignment;

DROP TABLE IF EXISTS user_task CASCADE;
DROP TABLE IF EXISTS authenticated_user_notif CASCADE;
DROP TABLE IF EXISTS invite_notif CASCADE;
DROP TABLE IF EXISTS task_notif CASCADE;
DROP TABLE IF EXISTS favorited CASCADE;
DROP TABLE IF EXISTS reply CASCADE;
DROP TABLE IF EXISTS post CASCADE;
DROP TABLE IF EXISTS project_member CASCADE;
DROP TABLE IF EXISTS task_comments CASCADE;
DROP TABLE IF EXISTS task CASCADE;
DROP TABLE IF EXISTS project CASCADE;
DROP TABLE IF EXISTS notif CASCADE;
DROP TABLE IF EXISTS admin CASCADE;
DROP TABLE IF EXISTS authenticated_user CASCADE;
DROP TABLE IF EXISTS pleas CASCADE;

DROP TYPE IF EXISTS priority;
DROP TYPE IF EXISTS "role";
DROP TYPE IF EXISTS status;

---------------------------------------------------------------

CREATE TYPE priority AS ENUM ('High', 'Medium', 'Low');
CREATE TYPE "role" AS ENUM ('Project member', 'Project manager', 'Project owner');
CREATE TYPE status AS ENUM ('Ongoing', 'On-hold', 'Finished');

---------------------------------------------------------------

CREATE TABLE authenticated_user (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_creation_date DATE DEFAULT CURRENT_DATE NOT NULL,
    suspended_status BOOLEAN NOT NULL,
    pfp VARCHAR(255),
    pronouns VARCHAR(50),
    bio TEXT,
    country VARCHAR(100),
	deleted_at TIMESTAMP DEFAULT NULL
);

CREATE TABLE admin (
    admin_id SERIAL PRIMARY KEY,
    admin_tag VARCHAR(255) UNIQUE NOT NULL,
    admin_username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE notif (
    notif_id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT CHECK (LENGTH(content) < 500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE project (
    project_id SERIAL PRIMARY KEY,
    availability BOOLEAN NOT NULL,
    project_creation_date DATE DEFAULT CURRENT_DATE NOT NULL,
    archived_status BOOLEAN NOT NULL,
    updated_at DATE,
    project_title VARCHAR(50) NOT NULL,
    project_description TEXT CHECK (LENGTH(project_description) < 500)

);

CREATE TABLE task (
    task_id SERIAL PRIMARY KEY,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE,
    task_name VARCHAR(255) NOT NULL,
    status status NOT NULL,
    details TEXT CHECK (LENGTH(details) < 500),
    due_date DATE CHECK (due_date > created_at),
    priority priority DEFAULT 'Medium' NOT NULL,
    created_at DATE DEFAULT CURRENT_DATE NOT NULL,
    updated_at DATE
);

CREATE TABLE task_comments (
    comment_id SERIAL PRIMARY KEY,
    id INTEGER REFERENCES authenticated_user(id) ON DELETE SET NULL,
    task_id INT REFERENCES task(task_id) ON UPDATE CASCADE ON DELETE CASCADE,
    comment TEXT CHECK (LENGTH(comment) < 500) NOT NULL,
    created_at DATE DEFAULT CURRENT_DATE NOT NULL
);

CREATE TABLE project_member (
    id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE,
    "role" "role" DEFAULT 'Project member' NOT NULL,
    PRIMARY KEY (id, project_id)
);

CREATE TABLE post (
    post_id SERIAL PRIMARY KEY,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE,
    id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
    content TEXT CHECK (LENGTH(content) < 500) NOT NULL,
    post_creation TIMESTAMP DEFAULT CURRENT_DATE NOT NULL
);

CREATE TABLE reply (
    reply_id SERIAL PRIMARY KEY,
    id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
    post_id INT REFERENCES post(post_id) ON UPDATE CASCADE ON DELETE CASCADE,
    content TEXT CHECK (LENGTH(content) < 500) NOT NULL,
    reply_creation TIMESTAMP DEFAULT CURRENT_DATE NOT NULL
);

CREATE TABLE favorited (
    id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE,
    checks BOOLEAN NOT NULL,
    PRIMARY KEY (id, project_id)
);

CREATE TABLE task_notif (
    task_notif_id SERIAL PRIMARY KEY,
    notif_id INT REFERENCES notif(notif_id)ON UPDATE CASCADE ON DELETE CASCADE,
    task_id INT REFERENCES task(task_id)ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE invite_notif (
    invite_notif_id SERIAL PRIMARY KEY,
    accepted BOOLEAN NOT NULL,
    notif_id INT REFERENCES notif(notif_id)ON UPDATE CASCADE ON DELETE CASCADE,
    project_id INT REFERENCES project(project_id)ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE authenticated_user_notif (
    id INTEGER NOT NULL REFERENCES authenticated_user(id) ON UPDATE CASCADE ON DELETE CASCADE,
    notif_id INTEGER NOT NULL REFERENCES notif(notif_id) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (id, notif_id)
);

CREATE TABLE user_task (
    id INT REFERENCES authenticated_user(id) ON UPDATE CASCADE ON DELETE CASCADE,
    task_id INT REFERENCES task(task_id) ON UPDATE CASCADE ON DELETE CASCADE,
    PRIMARY KEY (id, task_id)
);

CREATE TABLE pleas (
   pleas_id SERIAL PRIMARY KEY,
   authenticated_user_id INT REFERENCES authenticated_user(id)ON UPDATE CASCADE ON DELETE CASCADE,
   plea TEXT CHECK (LENGTH(plea) < 500) NOT NULL,
   created_at DATE DEFAULT CURRENT_DATE NOT NULL
);

CREATE TABLE password_reset_tokens (
   email VARCHAR PRIMARY KEY,
   token VARCHAR NOT NULL,
   created_at TIMESTAMP
);

CREATE TABLE follows (
     id SERIAL PRIMARY KEY,
     follower_id INTEGER NOT NULL,
     followed_id INTEGER NOT NULL,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
     CONSTRAINT fk_follower FOREIGN KEY (follower_id) REFERENCES authenticated_user(id) ON DELETE CASCADE,
     CONSTRAINT fk_followed FOREIGN KEY (followed_id) REFERENCES authenticated_user(id) ON DELETE CASCADE
);

ALTER TABLE authenticated_user
ADD COLUMN remember_token VARCHAR(100) NULL;

CREATE TABLE sessions (
  id VARCHAR PRIMARY KEY,
  user_id INT NULL REFERENCES authenticated_user(id) ON DELETE SET NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  payload TEXT NOT NULL,
  last_activity INT NOT NULL
);


CREATE INDEX sessions_user_id_index ON sessions(user_id);
CREATE INDEX sessions_last_activity_index ON sessions(last_activity);


CREATE FUNCTION update_task_and_project_timestamp() RETURNS TRIGGER AS $$

BEGIN
    -- Set the updated_at field for the task to the current timestamp when the task is modified
    NEW.updated_at := CURRENT_TIMESTAMP;

    -- Update the project’s updated_at field when the task is modified
    UPDATE project
    SET updated_at = CURRENT_TIMESTAMP
    WHERE project_id = NEW.project_id;

    RETURN NEW;  -- Return the modified task row
END
$$
LANGUAGE plpgsql;

CREATE TRIGGER update_task_and_project_timestamp_trigger
BEFORE UPDATE ON task
FOR EACH ROW
EXECUTE PROCEDURE update_task_and_project_timestamp();

-- Step 1: Create the function to notify user on task assignment
CREATE FUNCTION notify_task_assignment() RETURNS TRIGGER AS
$BODY$
DECLARE
    notif_title VARCHAR(255) := 'New Task Assignment';
    notif_content TEXT := 'You have been assigned a new task. Please review and start working on it as soon as possible.';
    new_notif_id INT;
BEGIN
    -- Insert notif into notif table
    INSERT INTO notif (title, content, created_at)
    VALUES (notif_title, notif_content, CURRENT_DATE)
    RETURNING notif_id INTO new_notif_id;
    -- Link the notif to the task and user
    INSERT INTO task_notif (notif_id, task_id) VALUES (new_notif_id, NEW.task_id);
    INSERT INTO authenticated_user_notif (id, notif_id) VALUES (NEW.id, new_notif_id);
    RETURN NEW;
END
$BODY$
LANGUAGE plpgsql;

CREATE TRIGGER TRIGGER_TASK_ASSIGN_notifY
AFTER INSERT ON user_task
FOR EACH ROW
EXECUTE PROCEDURE notify_task_assignment();

CREATE INDEX idx_post_creation_date ON post USING btree (post_creation);
-- Add a column to the project table to store computed ts_vectors for full-text search
ALTER TABLE project
ADD COLUMN ts_vector_title_description TSVECTOR;

-- Create a function to automatically update ts_vector_title_description
CREATE FUNCTION update_ts_vector_project() RETURNS TRIGGER AS
$BODY$
BEGIN
    NEW.ts_vector_title_description := 
        setweight(to_tsvector('english', NEW.project_title), 'A') ||
        setweight(to_tsvector('english', NEW.project_description), 'B');
    RETURN NEW;
END;
$BODY$
LANGUAGE plpgsql;

-- Create the trigger to call the function on insert or update
CREATE TRIGGER ts_vector_update_project
BEFORE INSERT OR UPDATE ON project
FOR EACH ROW EXECUTE FUNCTION update_ts_vector_project();

-- Create the GIN index for full-text search on the computed ts_vector column
CREATE INDEX idx_project_title_description ON project USING GIN (ts_vector_title_description);

-- Populate authenticated_user table
INSERT INTO authenticated_user (id, username, email, password, user_creation_date, suspended_status, pfp, pronouns, bio, country)
VALUES
    (1,'nmaseyk56177','natalya.maseyk@hotmail.com','kf!5*6!$3!*!&','2024-07-03 15:39:13',FALSE,'/tam/inimicus/paene/notissima.pfx','ze','Sic affecta, quem ad me accedis, saluto: ''chaere,'' inquam, ''Tite!'' lictores, turma omnis chorusque: ''chaere, Tite!'' hinc hostis mi Albucius, hinc inimicus. Sed iure Mucius. Ego autem mirari satis non queo unde hoc.','United States'),
	(2,'hyerrell58917','heall.yerrell@yahoo.com','e#!*n0s7**y#5','2024-10-05 12:46:49',TRUE,'/aut/pecuniae/studuisse/aut/quam.cue','it','Deorsum suo pondere ad lineam, hunc naturalem esse omnium corporum motum. Deinde ibidem homo acutus, cum illud ocurreret, si omnia dixi hausta e fonte naturae, si tota oratio nostra omnem sibi fidem sensibus confirmat, id est corpora individua propter soliditatem, censet in infinito inani, in quo nihil posset fieri minus; ita effici complexiones et copulationes et.','United States'),
	(3,'vbrimilcombe65125','virge.brimilcombe@gmail.com','$Zf!O&&&%H#!#','2024-10-20 20:07:49',TRUE,'/quibusdam/neque/nulli.jsp','they','Faciunt, qui ab eo dissentiunt, sed certe non probes, eum quem ego arbitror unum vidisse verum maximisque erroribus animos hominum liberavisse et omnia tradidisse.','United States'),
	(4,'emepham27336','evelyn.mepham@gmail.com','p$un*@!*%V6&%S*','2024-02-05 15:19:06',FALSE,'/est/quid/aut/ad/perveniri.odf','it','His, qui rebus infinitis modum constituant in reque eo meliore, quo maior sit, si nihil efficeret; nunc expetitur, quod est tamquam artifex conquirendae et comparandae voluptatis -- Quam autem ego dicam voluptatem, iam videtis, ne invidia.','United States'),
	(5,'emckimmey40901','emelina.mckimmey@hotmail.com','#!hI*gq%','2024-11-24 12:09:31',FALSE,'/usus/epicurus/qui/unum/genus/propter.html','she','Non horum morborum aliquo laborat, nemo igitur.','United States'),
	(6,'bkrahl22086','brunhilda.krahl@hotmail.com','!#5!*!02','2024-01-08 12:23:06',FALSE,'/doctissimos/summa/dissensio/quis/alienum/electis.jsp','he','Rationem sequamur monet. Nec enim satis est iudicare quid faciendum non faciendumve sit, sed stare etiam oportet in eo, qui ita sit affectus, et firmitatem animi nec mortem.','United States'),
	(7,'afrancino36096','allsun.francino@yahoo.com','0!S**!%m*','2024-04-27 11:05:41',FALSE,'/etsi/sit/graecis.bat','they','Illas reici, quia dolorem pariant, has optari, quia voluptatem. Iustitia restat, ut de.','United States'),
	(8,'eiles8380','elijah.iles@hotmail.com','!1&E5nj*g7&Z','2024-07-16 17:27:00',FALSE,'/qui/in/causa.ps1','ze','Hominum ac primorum signiferumque, maluisti dici. Graece ergo praetor Athenis, id quod maluisti, te, cum ad me accedis, saluto: ''chaere,'' inquam, ''Tite!'' lictores, turma.','United States'),
	(9,'dlevensky8282','dukey.levensky@gmail.com','*@59t8Pj196EJ','2024-07-12 05:13:11',TRUE,'/aliquem/confectum/tantis/fabulis.bash','ze','Vivendum reperiri posse, quod coniunctione tali sit aptius. Quibus ex omnibus iudicari potest non modo.','United States'),
	(10,'enasi23903','egon.nasi@yahoo.com','*b!!##t!%D3s%7z','2024-06-04 06:56:32',TRUE,'/sola/quae/inter.json','they','Terminari summam voluptatem, ut postea variari voluptas distinguique possit, augeri amplificarique non possit. At etiam Athenis, ut e patre audiebam facete.','United States'),
	(11,'mbrolechan23065','manolo.brolechan@hotmail.com','lB!3M!!016F!C$%x','2024-03-29 03:27:19',TRUE,'/vitae/posidonium.log','ze','Inciderit, ut id apte fieri possit, ut ab ipsis, qui eam disciplinam probant, non soleat accuratius explicari; verum enim invenire volumus, non tamquam adversarium aliquem convincere. Accurate autem quondam a L. Torquato, homine omni doctrina erudito.','United States'),
	(12,'sbuckles48250','saw.buckles@gmail.com','N$!4*&!@*','2024-05-12 19:59:23',FALSE,'/nec/naturales/nec/quamvis.priv','he','Tantum in alios caeco impetu incurrunt, sed intus etiam in animis inclusae inter se reprehensiones non sunt vituperandae, maledicta, contumeliae, tum iracundiae, contentiones concertationesque in disputando pertinaces indignae.','United States'),
	(13,'cdudeney58266','celestyna.dudeney@yahoo.com','*95*Jh5*#&!M!','2024-06-15 09:17:52',TRUE,'/vera/esse/non/ut.ods','she','''Tite!'' lictores, turma omnis chorusque: ''chaere, Tite!'' hinc hostis mi Albucius, hinc inimicus. Sed iure Mucius.','United States'),
	(14,'agibbett37554','antons.gibbett@gmail.com','*****m%!l!S!&Y%C','2024-07-26 22:51:53',FALSE,'/aut/omnia/semper/desperantes/sed.asc','they','Quod Graeci telos nominant --, quod ipsum nullam ad aliam rem, ad id omnia referri oporteat, ipsum autem nusquam. Hoc Epicurus.','United States'),
	(15,'kleades49648','karie.leades@yahoo.com','!e1I!K$f0H*4*G','2024-01-12 14:18:03',TRUE,'/quo/nihil/pluribus.ogg','ze','Patrioque amori praetulerit ius maiestatis atque imperii. Quid? T. Torquatus, is qui consul cum Cn. Octavio fuit, cum illam severitatem in.','United States'),
	(16,'tgarretts63398','thurston.garretts@hotmail.com','ZI6!i!J3','2024-01-09 18:49:06',FALSE,'/tria/vix/amicorum/paria/philosophorum.crt','they','Democritea dicit perpauca mutans, sed ita, ut.','United States'),
	(17,'lantoinet11052','law.antoinet@facebook.com','*L!o!%*G@AX!T8*','2024-01-12 01:14:13',FALSE,'/his/qui/rebus/infinitis/modum/nobis.html','he','Quadam percipitur sensibus, sed maximam.','United States'),
	(18,'kpoe3162','koressa.poe@gmail.com','8v9!$!*y!FD5uE2v','2024-03-04 19:57:45',TRUE,'/ob/eam/corrupisti.pssc','xe','Animi omnium rerum occultarum ignoratione sublata et moderatio natura cupiditatum.','United States'),
	(19,'cgroomebridge1376','codi.groomebridge@hotmail.com','*n4c6&*47*8','2024-04-08 06:54:51',TRUE,'/puto/modo/quae/dicat/ille/philosophari.mkv','they','Torquatos nostros? Quos tu paulo ante collegi, abest plurimum et, cum stultorum vitam cum sua comparat, magna afficitur voluptate. Dolores autem si qui incurrunt.','United States'),
	(20,'tswalwel29625','tobi.swalwel@gmail.com','2!*6L!#eV!s','2024-06-12 01:22:23',TRUE,'/alteram/non.iso','it','In hoc ipso vitae spatio amicitiae praesidium esse firmissimum.'' Sunt autem quidam Epicurei timidiores paulo contra vestra convicia, sed tamen satis acuti.','United States'),
	(21,'sbeniesh20298','stoddard.beniesh@aol.com','**z*!H*!!$y****Z','2024-04-03 14:39:01',FALSE,'/errore/et/qui.htm','he','Quae sua natura aut sollicitare possit aut angere. Praeterea et appetendi et refugiendi et omnino rerum gerendarum initia proficiscuntur aut a voluptate aut a voluptate aut a voluptate aut in liberos atque in sanguinem suum tam crudelis fuisse, nihil.','United States'),
	(22,'glusted55773','gerard.lusted@yahoo.co.uk','!g!f9!qh*9o6%*2','2023-12-05 01:41:28',TRUE,'/quem/didicerimus.tsv','ze','Nisi voluptatem efficerent, quis eas aut laudabilis aut expetendas arbitraretur? Ut enim aeque doleamus animo, cum corpore dolemus, fieri tamen permagna accessio potest, si.','United States'),
	(23,'bramiro38799','bertrand.ramiro@hotmail.com','!2&nW&**jB&9*!','2024-08-22 06:16:06',TRUE,'/equos/si/ludicra/est.ini','she','Non queo unde hoc sit tam insolens domesticarum rerum fastidium. Non.','United States'),
	(24,'echisman62741','enrica.chisman@gmail.com','$!Q&z*p$**L','2024-10-17 04:48:02',FALSE,'/enim/virtutes/adiit.psc1','xe','Aut ipse doctrinis fuisset instructior -- est enim, quod tibi ita videri necesse est, quid aut ad naturam aut contra sit.','United States'),
	(25,'abarnes19590','ansel.barnes@hotmail.co.uk','&79!m5P$&&*','2023-12-21 23:43:56',FALSE,'/eventurum/sit/provident/nihil.mov','ze','Eo libro, quo a populo Romano locatus sum, debeo profecto, quantumcumque possum, in eo essent. Quae cum dixissem.','United States'),
	(26,'cangus50657','christie.angus@hotmail.com','%P*M@Z!*hgDGf!*','2024-06-23 12:37:53',FALSE,'/illas/reici/quia/dolorem/pariant/pauca.psd1','it','Propter voluptates expetendam et insipientiam propter molestias esse fugiendam? Eademque ratione ne temperantiam quidem propter se ipsos penitus perdiderunt, sic robustus animus et a spe pariendarum voluptatum seiungi non potest. Atque ut odia, invidiae, despicationes adversantur voluptatibus, sic amicitiae non.','United States'),
	(27,'abigby25457','alanna.bigby@gmail.com','vK4s!PO!&%b*!u6O','2024-02-27 07:11:33',TRUE,'/aut/diuturnum/timeret/malum/quam.html','she','Ut de omni virtute sit dictum. Sed similia fere.','United States'),
	(28,'chumblestone24884','carolan.humblestone@aol.com','s@*zOG3#$5*$','2024-08-30 01:44:55',FALSE,'/quibusdam/hic.pfx','she','Usu venire; ut abhorreant a Latinis, quod inciderint in inculta quaedam et horrida, de malis Graecis Latine scripta.','United States'),
	(29,'dpawellek41488','denyse.pawellek@hotmail.com','02d57x6&*&AS','2024-06-18 23:12:18',FALSE,'/angusta/quam/magnos/quantaque/tamen.msh2xml','he','Interesse enim inter argumentum conclusionemque rationis et inter mediocrem animadversionem atque admonitionem. Altera occulta quaedam et quasi involuta aperiri, altera.','United States'),
	(30,'tfanshawe61212','tate.fanshawe@gmx.de','!!%W*!!r$4*P*3','2024-11-14 20:39:02',TRUE,'/qui/quae/pueros/insatiabiles.cer','he','Voluptate vivatur. Quoniam autem id est incorruptis atque integris testibus, si infantes pueri, mutae etiam bestiae paene loquuntur magistra ac duce natura nihil esse prosperum nisi voluptatem, nihil asperum nisi dolorem, de quibus et ab Epicuro.','United States'),
	(31,'jlangland650','jenica.langland@yahoo.com','Aq%X4%%!','2024-11-07 14:11:11',FALSE,'/multi/sint/epicurei/sunt/aliae/efficit.jsp','he','Dicere? Inesse enim necesse est effici, ut sapiens solum amputata circumcisaque inanitate omni et errore naturae finibus contentus sine aegritudine possit et sine metu.','United States'),
	(32,'nmangeot34779','nevin.mangeot@hotmail.com','&$E7!*!!9fz**9!','2024-01-27 12:26:11',FALSE,'/amicos/non/nulli/patriam/mox.log','they','Ea Latinis litteris mandaremus, fore ut atomus altera alteram posset attingere itaque ** attulit rem commenticiam: declinare dixit atomum perpaulum, quo nihil nec summum nec infimum nec medium nec ultimum nec extremum sit, ita ferri, ut concursionibus inter se cohaerescant, ex quo vitam amarissimam necesse est aut fastidii.','United States'),
	(33,'carnhold43583','conchita.arnhold@hotmail.com','*%0D0#&0!4w!k','2024-01-29 23:05:44',TRUE,'/etiam/amatoriis/philosophari.csv','xe','Factis illustribus et gloriosis satis hoc loco dictum sit. Erit enim iam de omnium virtutum cursu ad voluptatem proprius disserendi locus. Nunc autem explicabo, voluptas ipsa quae qualisque sit, ut tollatur error omnis imperitorum intellegaturque ea, quae dixi, sole ipso.','United States'),
	(34,'twanne29406','taddeo.wanne@yahoo.com','rTR5&!*y*sh','2023-12-14 10:58:28',FALSE,'/quae/quasi/titillaret/sensus/ut/ipsos.rar','it','Fortasse; tantum enim esse censet, quantus videtur, vel paulo.','United States'),
	(35,'apeck64213','aurie.peck@aol.com','$rP*7!*#4*iCL#$G','2024-07-13 12:11:18',FALSE,'/indignius/ad/maiora/necessariae.torrent','ze','Voluptatem pleniorem efficit. Itaque non ob ea solum.','United States'),
	(36,'nrichmond13831','nadya.richmond@charter.net','$*!#&4%1@%zc!*G','2023-12-04 11:31:53',FALSE,'/multa/habere/intervalla/atomorum.gif','ze','Grate meminit et praesentibus ita potitur, ut animadvertat quanta sint ea quamque.','United States'),
	(37,'mgrannell46517','marten.grannell@gmail.com','*1%59zrbc%T&','2023-12-25 13:48:22',FALSE,'/iure/reprehenderit/qui/in/ego.msh1','he','Existimant, quos quidem video esse a nostris non legantur? Quamquam, si plane sic verterem Platonem aut Aristotelem, ut verterunt nostri poetae fabulas, male, credo, mererer de meis civibus, si ad eorum cognitionem divina illa ingenia transferrem. Sed id neque feci adhuc nec.','United States'),
	(38,'egoning36950','ethelind.goning@aol.com','6*Yk!!&r!t!','2024-05-10 05:54:38',TRUE,'/sed/multo/etiam/magis/a.mshxml','it','Diligi et carum esse iucundum est propterea, quia tutiorem vitam et voluptatem ipsam.','United States'),
	(39,'ckamenar56236','cecil.kamenar@yahoo.com','*3y!@LE8','2024-08-12 02:00:08',FALSE,'/vivendum/sapientia/comparaverit/nihil/esse/notae.sql','xe','Tantum dissentio, ut, cum Sophocles vel optime scripserit Electram, tamen male conversam.','United States'),
	(40,'kmeffan12199','kenn.meffan@yahoo.com','*#x**$*m*$0!','2024-03-19 03:44:05',TRUE,'/quod/et/posse/athenis.md','ze','Scribere? Quodsi Graeci leguntur a Graecis isdem de rebus alia ratione compositis, quid est, quod nullam.','United States'),
	(41,'gsnazle15585','gus.snazle@mail.ru','!qzz7#*&5B@J*!*!','2024-09-17 23:22:42',TRUE,'/vigiliae/otiosum.htm','xe','Se esset et virtus et cognitio rerum, quod minime ille vult expetenda. Haec igitur Epicuri.','United States'),
	(42,'valbinson15344','vere.albinson@orange.fr','ax!ng!IS2','2024-10-03 06:43:00',FALSE,'/et/necessariae/alterum/quiddam.flac','they','Caelo est ad cognitionem omnium, regula, ad quam omnia iudicia.','United States'),
	(43,'tmegainey10301','tammi.megainey@aol.com','%!*5$0c!!6N!r0!G','2024-06-08 15:48:25',FALSE,'/cum/voluptate/provident.ini','xe','Voluptatem efficerent, quis eas aut laudabilis aut expetendas arbitraretur? Ut enim medicorum scientiam non ipsius artis, sed bonae valetudinis causa probamus, et gubernatoris ars, quia bene navigandi rationem habet, utilitate, non arte laudatur, sic sapientia, quae ars vivendi putanda est, non satis politus iis.','United States'),
	(44,'ile barr19994','iorgo.lebarr@msn.com','lmJ##!A4','2024-06-30 11:30:09',FALSE,'/contra/meo.csv','they','Causa? Quae fuerit causa, mox videro; interea hoc tenebo, si ob aliquam causam ista, quae sine dubio praeclara sunt, fecerint, virtutem iis per se esset et virtus et cognitio rerum, quod minime ille vult expetenda. Haec igitur Epicuri non probo, inquam. De cetero vellem.','United States'),
	(45,'anorwich18573','adolpho.norwich@hotmail.com','I*!**XuE&**0','2023-12-06 19:58:58',TRUE,'/tite/lictores/aut.rar','it','Refugiendi et omnino rerum gerendarum initia proficiscuntur aut a dolore. Quod cum ita sit, perspicuum est omnis rectas res atque laudabilis eo referri, ut cum voluptate vivere. Nec enim satis est iudicare quid faciendum non faciendumve.','United States'),
	(46,'jwinspire2931','jemima.winspire@gmail.com','@!sCc**!3*$!*!','2024-11-11 04:38:24',TRUE,'/fugiat/nulla/pariatur/excepteur/sint/me.mds','it','Dissentiet -- quod Graeci telos nominant --, quod ipsum nullam ad aliam rem, ad id autem res referuntur omnes, fatendum est summum esse bonum quicquam nisi nescio quam illam umbram, quod appellant honestum non tam solido.','United States'),
	(47,'ccrossman17613','consuela.crossman@yahoo.com','9%N#!r!*$**D!!','2024-08-12 23:21:43',TRUE,'/quod/graeci/non.msh1','she','Fabulis delectari dicat, Latinas litteras oderit?','United States'),
	(48,'nadao55185','neala.adao@gmail.com','6s*F&*&80','2024-03-15 04:53:57',FALSE,'/locum/petentium/sine/causa/sed.flac','he','Careat, dolor in reprehenderit in voluptate ponit, quod summum bonum diceret, primum in eo non arbitrantur. Erunt etiam, et ii quidem eruditi Graecis litteris, contemnentes Latinas, qui se plane Graecum dici velit.','United States'),
	(49,'npearce38766','norris.pearce@live.nl','cDZP*!j!','2024-06-19 14:33:38',TRUE,'/corpore/voluptatibus/nullo/dolore/vivatur.pgp','ze','Id, de quo quaerimus, non quo ignorare vos arbitrer, sed ut ratione et via procedat.','United States'),
	(50,'hnuschke22822','hal.nuschke@gmail.com','8*id*!!2*n*&Ifb','2024-09-04 16:59:14',FALSE,'/seiungi/non/potest/atque/ut/praesidium.sh','ze','Esse prosperum nisi voluptatem, nihil asperum nisi dolorem, de quibus ante dictum est, sic amicitiam negant posse a voluptate discedere. Nam cum.','United States'),
	(51,'rlonergan51342','ranice.lonergan@rediffmail.com','***RsH!V','2024-01-25 22:55:48',TRUE,'/sit/a/triari.txt','she','Quamquam, si plane sic verterem Platonem aut Aristotelem, ut verterunt nostri poetae fabulas, male, credo.','United States'),
	(52,'kdunseith34391','kelbee.dunseith@uol.com.br','*j!#x!!5k*Z#*&Y9','2024-04-16 23:12:49',TRUE,'/si/aeque/diu/sit/vim.cfg','they','Molestias esse fugiendam? Eademque ratione ne temperantiam quidem propter se esse fugiendam temperantiamque expetendam, non quia voluptates fugiat, sed quia maiores consequatur. Eadem fortitudinis.','United States'),
	(53,'lsouthern16721','lory.southern@yahoo.com','9%!*au$3!v8%B','2024-07-29 08:43:18',TRUE,'/autem/privatione/molestia.iso','xe','Declarant, in quibus tam multis tamque.','United States'),
	(54,'kcolqueran1491','kaine.colqueran@hotmail.fr','s!v!b#!%Lvm*#9','2024-04-13 18:24:22',FALSE,'/democritea/dicit/perpauca/multi.key','it','Nosque ea scripta reliquaque eiusdem generis et legimus libenter et legemus --, haec, quae vitam omnem continent, neglegentur? Nam, ut sint opera, studio, labore meo doctiores.','United States'),
	(55,'froxburch10679','fremont.roxburch@gmail.com','1gCI*Ux#!b','2024-05-24 07:56:47',TRUE,'/contemnentes/latinas/qui/se/plane/maiores.rar','she','Multa nec impensa expleantur; ne naturales quidem multa desiderant, propterea quod ipsa natura, ut ait ille, sciscat et probet, id est in quo nihil posset fieri minus; ita effici complexiones et copulationes et adhaesiones atomorum inter se, ex quo vitam amarissimam necesse.','United States'),
	(56,'kde meyer58022','kirstin.demeyer@gmail.com','8!N!36l*!F2uEH%!','2024-07-15 12:00:46',FALSE,'/non/hominum.doc','he','Te hortatore facimus, consumeret, in quibus hoc primum est in quo nihil.','United States'),
	(57,'ho''carroll50224','hayes.ocarroll@yahoo.com','91U*1C!!!','2024-10-04 03:36:45',FALSE,'/libro/quem/ad/tibique.jsp','xe','Dissidens secumque discordans gustare partem ullam liquidae voluptatis et liberae.','United States'),
	(58,'bcaws49068','bennett.caws@yahoo.com','v3*!Ns#0*&','2024-11-02 15:41:53',TRUE,'/magna/afficitur/voluptate/dolores/autem/dolorem.lnk','xe','Legantur? Quamquam, si plane sic verterem Platonem aut Aristotelem, ut verterunt nostri poetae fabulas, male, credo, mererer de.','United States'),
	(59,'lcordall54513','lauritz.cordall@aol.com','T!**!k*!3!3ZL#!*','2024-03-06 02:32:58',TRUE,'/desideraret/quia/quod/dolore/epicuri.html','ze','Sunt tota Democriti, atomi, inane, imagines, quae eidola nominant, quorum incursione non solum praesentibus fruuntur, sed etiam effectrices sunt voluptatum tam amicis quam sibi, quibus non solum praesentibus fruuntur, sed etiam spe eriguntur consequentis ac posteri temporis. Quod quia nullo modo sine amicitia firmam et perpetuam iucunditatem vitae tenere possumus neque vero ipsam amicitiam tueri, nisi.','United States'),
	(60,'mbanat43096','mahmud.banat@hotmail.com','%Cp$3As3','2023-11-30 10:11:18',TRUE,'/celeritas/diuturnitatem/non.conf','she','Quem tandem hoc statu praestabiliorem aut magis expetendum possimus dicere? Inesse enim necesse est in voluptate. Neque enim disputari sine reprehensione nec cum iracundia aut pertinacia recte disputari.','United States'),
	(61,'mdubble49213','millie.dubble@yahoo.com','Y*U06F!#54i','2024-07-15 12:47:07',TRUE,'/enim/satis/est/efficit.gz','it','Magnitudinem celeritas, diuturnitatem allevatio consoletur. Ad ea cum accedit, ut neque divinum numen horreat nec praeteritas voluptates effluere patiatur earumque.','United States'),
	(62,'hsimounet12424','hubey.simounet@gmail.com','mA&P$*xp4xNZN!','2024-06-08 00:46:22',FALSE,'/impediri/rationem/amicitiae/posse.scf','she','Nunc expetitur, quod est tamquam artifex conquirendae.','United States'),
	(63,'rmandal45426','robinet.mandal@hotmail.com','H$L7!37**!*6*g','2024-06-11 10:14:48',FALSE,'/firme/graviterque/comprehenderit/si.tar','it','Non fuisse. -- Torquem detraxit hosti. -- Et quidem se texit, ne interiret. -- At.','United States'),
	(64,'mcrangle22028','merola.crangle@hotmail.fr','v$*%#@i!!!oj%','2024-07-27 04:48:24',FALSE,'/futura/modo/novum.ppt','she','Constituam, quid et quale sit id, de quo quaerimus, non quo modo efficiatur concludaturque ratio tradit, non qua via captiosa solvantur ambigua distinguantur ostendit.','United States'),
	(65,'lsicily2544','lisette.sicily@yahoo.com','T#*%N*2*!*!&1!4#','2024-02-22 11:56:47',FALSE,'/cum/solitudo/et/suum.txt','xe','Quam eorum utrumvis, si aeque diu sit in corpore. Non placet autem detracta voluptate aegritudinem statim consequi, nisi in voluptatis locum dolor forte successerit, at contra gaudere nosmet omittendis doloribus, etiamsi voluptas ea, quae corrigere vult, mihi quidem nulli satis eruditi videntur.','United States'),
	(66,'psuddock11484','phebe.suddock@yahoo.com','t41r1!##V8**','2024-05-09 00:31:44',TRUE,'/distinctio/traditur/restat/error.psm1','she','Diceret, primum in eo non arbitrantur. Erunt etiam, et ii quidem eruditi Graecis litteris, contemnentes Latinas, qui se Latina scripta dicunt contemnere. In quibus hoc primum est in voluptate ponit, quod summum bonum.','United States'),
	(67,'cfritche18984','cammie.fritche@gmail.com','WG#mmB!Ln','2024-07-30 09:51:20',TRUE,'/reiciat/quod/se/cuiquam.mp4','xe','Animis, quae nos a libidinum impetu.','United States'),
	(68,'rdoerren61584','rem.doerren@gmail.com','eH*09!3x!6g$k','2024-11-19 09:56:03',TRUE,'/dicunt/electram.mkv','xe','Legimus tamen Diogenem, Antipatrum, Mnesarchum, Panaetium, multos alios in primisque familiarem nostrum Posidonium. Quid? Theophrastus mediocriterne.','United States'),
	(69,'kportwaine47859','karry.portwaine@yahoo.com','$s*!m6j!*y!2ka*','2024-11-10 06:45:26',FALSE,'/magnitudinem/celeritas/multo.mov','she','Placeat, facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet, ut et adversa quasi perpetua oblivione obruamus et secunda iucunde ac.','United States'),
	(70,'aantonellini28487','antony.antonellini@yahoo.com','K%28#%U!9','2024-02-12 00:56:34',FALSE,'/sit/nec.bz2','she','Voluptate vivere. Huic certae stabilique sententiae quae sint quaeque cernantur, omnia, eumque motum atomorum nullo a principio, sed ex aeterno tempore intellegi convenire. Epicurus autem, in quibus hoc primum est in eo, quod sit a dolore corporis praesenti futurove seiunctum. Nec vero hoc oratione solum, sed multo etiam magis, quod, cuius in.','United States'),
	(71,'gcaveney55701','garreth.caveney@gmail.com','81!$*&*%&18UK4','2023-11-28 11:46:03',FALSE,'/feci/adhuc/nec/mihi/tamen/dolore.gif','he','Praesentibus non fruuntur, futura modo expectant, quae quia certa esse non possunt et, si essent vera, nihil afferrent, quo iucundius, id est in quo nihil turpius physico, quam fieri quicquam sine causa dicere, -- et illum.','United States'),
	(72,'cchasmor57345','chaddie.chasmor@yahoo.com','J**!#!%!%J#nj!E','2024-08-12 14:03:45',TRUE,'/non/eram/nescius/brute/non.odf','she','Sequatur natura ut summum malum et, quantum possit, a se ipse dissidens.','United States'),
	(73,'ssuddell64513','skelly.suddell@hotmail.com','!*1!Lt#*&!3YOq','2024-07-31 00:12:33',TRUE,'/discordia/intellegitur.asc','she','Epicurus, is quem vos nimis voluptatibus esse deditum dicitis; non posse iucunde vivi.','United States'),
	(74,'cgoford40217','chiquia.goford@gmail.com','!jA$&YQ!qbZkyk','2024-06-20 03:42:20',TRUE,'/fit/ut/aegritudo/sequatur/si/facta.msh2xml','it','Mihi nullo modo poterimus sensuum iudicia defendere. Quicquid porro animo cernimus, id omne oritur a sensibus; qui si omnes veri erunt, ut Epicuri ratio docet, tum denique poterit aliquid cognosci et percipi. Quos qui tollunt et.','United States'),
	(75,'kregi4513','ketti.regi@gmail.com','%**!*x%9B','2024-03-07 08:53:05',TRUE,'/beatus/multoque/in.mkv','it','Ista sequimur, ut sine cura metuque vivamus animumque et corpus, quantum efficere possimus, molestia liberemus. Ut enim mortis metu omnis quietae.','United States'),
	(76,'fdudden15720','flem.dudden@hotmail.com','%Vx8jU!W*5*w!!','2024-10-05 20:40:38',FALSE,'/lucifugi/maledici/monstruosi/alii/autem/vitam.txt','ze','Epicurus ineruditus, sed ii indocti, qui, quae pueros non didicisse turpe est, ea putant usque ad senectutem esse discenda. Quae cum tota res (est) ficta pueriliter, tum ne efficit quidem.','United States'),
	(77,'dpudsey39708','desdemona.pudsey@gmail.com','!Z*#b!P$3P**3h','2024-03-17 23:05:47',TRUE,'/inquam/triari/maluisti.torrent','she','Iudicatum. Plerique autem, quod tenere atque servare id, quod propositum.','United States'),
	(78,'koyley2930','karola.oyley@hotmail.com','r6!!*$17O#8#','2024-10-19 04:08:29',FALSE,'/audivi/cum/miraretur/inpotenti.mov','they','Complectitur verbis, quod vult, et dicit plane, quod intellegam; et tamen in quibusdam neque pecuniae modus est ullus investigandi veri, nisi inveneris, et quaerendi defatigatio turpis est, cum id, quod ipsi statuerunt.','United States'),
	(79,'mshewery41843','mickie.shewery@qq.com','C!#u#!05R*','2024-02-21 20:03:53',TRUE,'/usus/civium/non/quae.sh','it','Faciant id, quod sentiant non esse faciendum, ii voluptatem maximam adipiscuntur praetermittenda voluptate. Idem etiam dolorem saepe perpetiuntur.','United States'),
	(80,'cletessier59795','cobbie.letessier@yahoo.com','DmZ*!&$#RcbRs#$*','2024-09-11 11:19:41',FALSE,'/omnino/hic/docendi/locus/nos.bz2','xe','Epularum nec reliquarum cupiditatum, quas nulla praeda umquam improbe parta minuit.','United States'),
	(81,'mdederich51622','morgen.dederich@gmail.com','Vj%*g&xZ2*8k*','2024-02-20 11:42:19',TRUE,'/nullo/possit.csv','it','Cogitavisse? Sed ut perspiciatis, unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam eaque ipsa, quae laudatur, industria, ne fortitudo quidem, sed ista sequimur, ut sine cura metuque vivamus animumque et corpus, quantum efficere possimus, molestia liberemus. Ut enim aeque doleamus animo, cum corpore.','United States'),
	(82,'epyatt510','eugenio.pyatt@hotmail.com','!06e$*$IS&t','2024-08-01 04:01:23',TRUE,'/non/fruuntur/futura/modo/in.xml','ze','Et comparandae voluptatis -- Quam autem ego dicam voluptatem, iam videtis, ne invidia verbi.','United States'),
	(83,'scarrack15011','sigrid.carrack@hotmail.com','LlA!l*330A!F9s','2024-01-23 07:51:54',TRUE,'/cuius/in/mente/maiestatis.msh','they','Praebeat ad voluptatem. Sapientia enim est a Cyrenaicisque melius liberiusque defenditur, tamen eius modi esse iudico, ut nihil homine videatur indignius. Ad maiora enim.','United States'),
	(84,'jconningham31493','julita.conningham@gmail.com','&X&!%*n!&#2','2024-08-29 01:55:27',TRUE,'/expetendam/et/insipientiam/propter/illis.ps1xml','she','Maximisque erroribus animos hominum liberavisse et omnia tradidisse.','United States'),
	(85,'bmanie339','bartholomeus.manie@hotmail.com','$!#**w!V#D0u%x!','2024-03-17 10:57:43',FALSE,'/ut/de/omni/hominum.mshxml','xe','Et metu maximeque cruciantur, cum sero sentiunt frustra se aut pecuniae studuisse aut imperiis aut opibus aut gloriae. Nullas enim consequuntur voluptates, quarum potiendi.','United States'),
	(86,'mpopland4422','moyna.popland@hotmail.com','H0h%#%*!7*MZ','2024-07-17 23:55:28',FALSE,'/ac/ratione/intellegi/posse/intellegerem.rpm','he','Et laetitiam nobis voluptas animi et molestiam dolor.','United States'),
	(87,'oeasbie44417','orel.easbie@gmail.com','q%XV1%2S@','2024-03-09 12:35:51',TRUE,'/est/quaerendi/ac/disserendi/non.aspx','he','Quibusdam stabilitas amicitiae vacillare, tuentur tamen eum locum seque facile, ut mihi quidem depravare videatur. Ille atomos quas appellat, id est voluptatem. Homines optimi non intellegunt totam rationem everti, si ita res se habeat. Nam si concederetur, etiamsi ad corpus referri, nec ob eam causam non multo maiores esse et muniti videntur.','United States'),
	(88,'eblanko24115','eulalie.blanko@hotmail.fr','9!LA*zJ*a!B*9!H','2024-02-05 08:22:56',FALSE,'/quam/illam/umbram/quod/utriusque.jsp','it','Quos nominavi, cum inciderit, ut id apte fieri possit, ut ab ipsis, qui eam disciplinam probant, non soleat accuratius explicari; verum enim.','United States'),
	(89,'cedyson8323','christoforo.edyson@yahoo.com.au','!oT!*!$9$5','2023-12-13 03:56:50',TRUE,'/consequatur/eadem/fortitudinis/ratio/desiderant.html','they','Videantur. Vide, quantum, inquam, fallare, Torquate. Oratio me istius philosophi non offendit; nam et complectitur verbis, quod vult, et dicit plane, quod intellegam; et tamen ego a philosopho, si afferat eloquentiam, non asperner, si non habeat, non admodum indoctis, totum hoc displicet.','United States'),
	(90,'acainey62637','andromache.cainey@yahoo.com','*FyG&**qk%!7*4','2024-02-27 06:16:49',FALSE,'/ut/aliquid/ex/ea/voluptate.csv','xe','Esset affecta, secundum non recte, si voluptas summum sit bonum, affirmatis nullam omnino fore. De qua Epicurus quidem ita dicit.','United States'),
	(91,'kvan halle61711','kennedy.vanhalle@gmail.com','$$M%&6lB','2024-03-04 03:52:31',TRUE,'/epicurus/quidem/ita/dicit/omnium/omnes.yml','she','Quidem, sicut alia; sed neque tam docti.','United States'),
	(92,'jpinckstone37006','job.pinckstone@yahoo.com','bVL*&1*!!umV','2024-01-17 11:43:29',FALSE,'/placuit/possent.mdb','xe','Sed illas reici, quia dolorem pariant, has optari, quia voluptatem. Iustitia.','United States'),
	(93,'bgianelli31338','bennett.gianelli@outlook.com','Z*$S!j#*%36*!%','2024-09-29 23:53:09',TRUE,'/ostendit/iudicia/rerum/ea.css','xe','Quidem cedentem in philosophia audeam scribere? Quamquam a te ipso id quidem.','United States'),
	(94,'vbachellier61789','valaria.bachellier@yahoo.com','#h#*2!N4*!!','2024-10-31 01:04:35',TRUE,'/ut/necessariae/denique.scf','ze','Ac ratione intellegi posse et voluptatem ipsam per se esset et virtus.','United States'),
	(95,'fsloat6370','fancie.sloat@hotmail.com','%y!$!*!yvu1r!&','2024-06-30 07:09:47',FALSE,'/finis/dicere.pptx','she','Conciliant et, quod aptissimum est ad quiete vivendum, caritatem, praesertim cum omnino nulla sit.','United States'),
	(96,'vdreini40894','verge.dreini@hotmail.it','4*!14AY3!*#!7%','2024-02-09 10:41:27',TRUE,'/avocent/a/philosophia/quam/se.sh','xe','Modo sapiens erit affectus erga amicum, quo in se ipsum, quosque labores propter suam voluptatem susciperet, eosdem suscipiet propter amici voluptatem. Quaeque de virtutibus dicta sunt, quem ad modum affecta nunc est, desiderat?'' -- Nihil sane. -- ''At, si voluptas.','United States'),
	(97,'rstigell8352','roseline.stigell@gmail.com','1*9!!t&#','2024-04-02 06:05:30',FALSE,'/esse/utuntur.bmp','she','Honestum non tam id reprehendunt, si remissius agatur, sed tantum studium tamque multam operam ponendam in eo ipso parum vidit, deinde hoc quoque alienum; nam ante Aristippus, et ille melius. Addidisti ad extremum etiam indoctum fuisse. Fieri, inquam, Triari, nullo.','United States'),
	(98,'bmellonby51206','barn.mellonby@yahoo.com','!a!!*0*F','2024-09-28 03:05:56',TRUE,'/id/voluptatem.pptx','ze','Ipsum, quia dolor sit, amet, consectetur, adipisci velit, sed quia non numquam eius modi esse iudico, ut nihil homine videatur indignius. Ad maiora enim quaedam nos natura genuit et conformavit, ut mihi quidem nulli satis eruditi.','United States'),
	(99,'cwoods13557','cristy.woods@wanadoo.fr','Coh&32!9zn!','2024-09-08 12:46:35',TRUE,'/summum/ex/infinitum.png','xe','Chremes non inhumanus, qui novum.','United States'),
	(100,'cbiggin33711','catherine.biggin@hotmail.com','Yy*!Xw*!!','2024-06-05 20:41:42',FALSE,'/tum/non.pdf','xe','Minus, animo aequo e vita, cum ea non placeat, tamquam e theatro exeamus. Quibus rebus intellegitur nec intemperantiam propter se ipsos amentur.','United States'),
    (101, 'john_doe', 'john@example.com', 'password123', CURRENT_DATE, FALSE, 'profile1.jpg', 'he/him', 'Software Developer', 'USA'),
    (102, 'jane_smith', 'jane@example.com', 'password456', CURRENT_DATE, FALSE, 'profile2.jpg', 'she/her', 'Project Manager', 'UK'),
    (103, 'alice_wong', 'alice@example.com', 'password789', CURRENT_DATE, TRUE, 'profile3.jpg', 'they/them', 'Designer', 'Canada');

-- Populate admin table
INSERT INTO admin (admin_tag, admin_username, password)
VALUES
    ('admin01', 'admin_john', 'adminpass1'),
    ('admin02', 'admin_jane', 'adminpass2'),
    ('admin123','SuperAdmin','$2y$10$Tj4Y8c0Zt0lbuOKmJI14/.lNoHD54UkLI2xHpTsa5Pb6OnfacMhnW');

-- Populate notif table
INSERT INTO notif (title, content, created_at)
VALUES
    ('Key Finder','Imitarentur, ullus orationis vel copiosae vel elegantis.','2024-02-15 12:00:01'),
	('Interactive Robot Toy','Dicunt contemnere. In quibus tam multis tamque variis ab ultima antiquitate repetitis tria vix amicorum paria reperiuntur, ut ad id omnia referri oporteat, ipsum autem nusquam. Hoc Epicurus.','2024-05-03 08:28:52'),
	('Honey Garlic Shrimp','Quidem locum comit multa venustate et omni sale idem Lucilius, apud quem praeclare Scaevola: Graecum te, Albuci, quam Romanum atque Sabinum, municipem Ponti, Tritani, centurionum, praeclarorum hominum ac primorum signiferumque, maluisti dici.','2024-09-06 15:44:09'),
	('Gardening Tools Set','Antiqua persequeris, claris et fortibus viris commemorandis eorumque factis non emolumento aliquo.','2023-12-03 10:57:28'),
	('Flavored Rice Cakes','Sumitur contra mortis timorem et constantia contra metum religionis et sedatio animi omnium rerum occultarum ignoratione sublata et moderatio natura cupiditatum generibusque earum explicatis, et, ut modo docui, cognitionis regula et iudicio ab eadem illa constituto veri a falso distinctio traditur. Restat.','2024-07-29 14:18:48'),
	('Quinoa','Longinquitate levis, in gravitate brevis soleat esse, ut eius magnitudinem celeritas, diuturnitatem allevatio consoletur. Ad ea cum accedit, ut neque divinum.','2024-08-13 07:25:09'),
	('Sweet Corn','A meque ei responsum, cum C. Triarius, in primis gravis et doctus adolescens, ei disputationi interesset. Nam cum ad me in Cumanum salutandi causa uterque venisset, pauca primo.','2024-05-17 13:16:23'),
	('Fettuccine Alfredo Dinner Kit','Satis esse admonere. Interesse enim inter argumentum conclusionemque rationis et inter mediocrem animadversionem atque admonitionem. Altera occulta quaedam et quasi architecto.','2024-05-29 06:53:45'),
	('Classic Watch','Idem Graecum, quae autem de bene beateque vivendum. Sed existimo te, sicut nostrum Triarium, minus ab eo dissentiunt, sed certe non probes, eum quem ego arbitror unum vidisse verum maximisque.','2024-08-26 17:40:25'),
	('Chocolate Dipped Fruit','Potuimus, non modo singulos homines, sed universas familias evertunt, totam.','2023-12-16 16:57:36'),
	('Set of Herb Garden Markers','Praetermissum in Stoicis? Legimus tamen Diogenem, Antipatrum, Mnesarchum, Panaetium, multos alios in primisque familiarem nostrum Posidonium. Quid? Theophrastus mediocriterne delectat, cum tractat locos ab Aristotele ante tractatos? Quid? Epicurei num desistunt de isdem, de quibus ante dictum est, sic.','2024-07-29 01:06:59'),
	('Natural Peanut Butter','Aliquando otiosum, certe audiam, quid sit, quod Epicurum nostrum non tu quidem oderis, ut fere faciunt, qui ab eo ortum, tam inportuno.','2024-04-03 00:42:23'),
	('Overnight Duffle Bag','Scribimus, quis est tam invidus, qui ab eo.','2024-02-06 03:19:42'),
	('Wrap Front Midi Skirt','Iudex; Multi etiam, ut te consule, ipsi se indicaverunt. Quodsi qui satis sibi contra hominum conscientiam saepti esse et voluptates et dolores nasci fatemur e corporis voluptatibus et doloribus .','2024-07-15 18:01:19'),
	('Fitness Activity Journal','Partis confirmatur animus et excelsus omni est liber cura et angore, cum.','2024-05-23 13:06:38'),
	('Outdoor Camping Hammock','Corrigere voluit, deteriora fecit. Disserendi artem nullam habuit. Voluptatem cum.','2024-02-03 12:58:09'),
	('Maple Bacon Jerky','Quae recte, quae oblique ferantur, deinde eadem illa constituto veri a falso distinctio traditur. Restat locus huic disputationi vel maxime necessarius de amicitia, quam, si voluptas esset bonum, desideraret.'' -- Ita credo. -- ''Non est igitur voluptas bonum.'' Hoc ne.','2024-11-09 07:08:51'),
	('Magnetic Puzzle Board','Voluptatem, quia voluptas sit, aspernatur aut odit aut fugit, sed.','2024-01-30 21:34:20'),
	('Magnetic Phone Car Mount','Perferendis doloribus asperiores repellat. Hanc ego cum teneam sententiam, quid.','2024-02-16 17:58:14'),
	('Sliced Bread','Est praetore salutatus Athenis Albucius. Quem quidem locum comit multa venustate et omni sale idem Lucilius, apud quem praeclare Scaevola: Graecum.','2024-08-01 08:51:38'),
	('Cranberry Pecan Granola','-- Nihil sane. -- ''At, si voluptas esset bonum, fuisse.','2024-01-13 16:40:44'),
	('Microwave Popcorn Maker','Numeranda nec in malis dolor, non existimant oportere.','2024-01-17 11:45:47'),
	('Cauliflower Gnocchi','Ratione dici non necesse est. Tribus igitur modis video esse multos, sed imperitos --, quamquam autem et laetitiam nobis voluptas animi et molestiam dolor afferat, eorum tamen utrumque et ortum esse e corpore.','2024-05-28 09:04:38'),
	('Vegetarian Sausage Links','Quo intellegitur nec timiditatem ignaviamque vituperari nec fortitudinem patientiamque laudari suo nomine, sed illas reici, quia dolorem pariant, has optari, quia.','2024-08-23 12:06:42'),
	('Almond Joy Snack Bars','Conscientia factorum, tum poena legum odioque civium? Et tamen in quibusdam neque pecuniae modus est neque honoris neque imperii nec libidinum nec epularum nec.','2024-04-17 01:58:28'),
	('Garlic Butter Shrimp','Salutandi causa uterque venisset, pauca primo inter nos de litteris, quarum summum erat in utroque studium, deinde Torquatus: Quoniam nacti te, inquit, sumus aliquando otiosum, certe audiam, quid sit, quod Epicurum nostrum non tu quidem oderis, ut fere faciunt, qui ab eo et gravissimas.','2024-09-19 11:20:47'),
	('Mixed Nuts','Sapienti maximasque ab eo et gravissimas res consilio ipsius et ratione administrari neque maiorem voluptatem.','2024-08-03 11:49:49'),
	('Sliced Strawberries','Recordatione renovata delectant. Est autem situm in nobis ut et voluptates et dolores nasci fatemur e corporis voluptatibus et doloribus -- itaque concedo, quod modo dicebas, cadere causa.','2024-10-24 21:02:33'),
	('Pet Waterer with Filtration','Dare, quae recte, quae oblique ferantur, deinde eadem illa constituto veri a falso distinctio traditur. Restat locus huic disputationi vel maxime necessarius de amicitia, quam, si voluptas summum sit bonum, affirmatis nullam omnino fore. De qua.','2024-10-12 17:29:19'),
	('Insulated Lunch Box','Illa constituto veri a falso distinctio traditur. Restat locus huic disputationi vel maxime necessarius de amicitia, quam, si voluptas summum sit bonum, affirmatis nullam omnino fore. De qua omne certamen est? Tuo vero id quidem, inquam, arbitratu. Sic faciam.','2024-08-19 17:45:44'),
	('Pet Tracking Collar','Cognitioque rerum, quid poetarum evolutio, quid tanta tot versuum memoria voluptatis affert? Nec mihi tamen, ne faciam, interdictum puto. Locos quidem quosdam, si videbitur, transferam, et maxime ab iis, quos.','2024-08-10 13:26:30'),
	('Fitness Tracker with Heart Rate Monitor','Vitam amarissimam necesse est in quo admirer, cur in gravissimis rebus non.','2024-04-27 12:43:45'),
	('Paprika','Haec igitur Epicuri non probo, inquam. De cetero vellem equidem aut ipse doctrinis fuisset instructior -- est enim, quod tibi ita videri necesse est, quid aut.','2024-04-11 15:22:55'),
	('Camping Tent','Est, quod nullam eruditionem esse duxit, nisi quae beatae vitae deduceret?','2024-02-17 11:03:10'),
	('Coconut Cream Pie Mix','His, qui rebus infinitis modum constituant in reque eo meliore, quo maior.','2024-06-15 23:37:05'),
	('Field Journal','Defuit? Ego vero, quoniam forensibus operis, laboribus, periculis non deseruisse mihi videor praesidium, in quo etiam Democritus haeret, turbulenta concursio hunc mundi ornatum efficere non poterit. Ne illud quidem perspicuum est, maximam animi aut voluptatem aut molestiam plus aut.','2023-12-03 13:43:36'),
	('Organic Black Beans','Reliquaque eiusdem generis et legimus libenter et legemus .','2024-09-29 07:50:41'),
	('Vintage Graphic Tee','Omnia referri oporteat, ipsum autem nusquam. Hoc Epicurus in voluptate esse aut in armatum hostem impetum fecisse aut in dolore. Omnis autem privatione doloris putat Epicurus terminari summam voluptatem, ut ea maior sit, mediocritatem desiderent. Sive enim ad sapientiam perveniri.','2024-02-04 19:06:14'),
	('Oven-Baked Sweet Potato Fries','Est. Quam a nobis explicatam esse his litteris arbitramur, in quibus, quantum.','2024-08-16 23:59:49'),
	('Garden Hoses with Expandable Features','Enim ipsa mihi sunt voluptati, et erant illa Torquatis.'' Numquam hoc ita defendit Epicurus neque Metrodorus aut quisquam eorum, qui aut saperet aliquid aut ista didicisset. Et quod quaeritur saepe.','2024-11-10 16:00:42'),
	('Car Phone Mount','Praeceptrice in tranquillitate vivi potest omnium cupiditatum ardore restincto. Cupiditates enim sunt insatiabiles, quae non modo non inopem, ut vulgo putarent, sed locupletiorem etiam esse quam Graecam. Quando enim nobis, vel dicam aut oratoribus bonis aut poetis, postea quidem quam fuit quem imitarentur, ullus orationis vel copiosae vel elegantis ornatus defuit? Ego vero, quoniam forensibus operis.','2024-08-30 10:02:02'),
	('Chickpea Salad','Uti velint vel, si suas habent, illas non magnopere desiderent. Qui autem alia malunt scribi a nobis, aequi esse debent, quod et scripta multa.','2024-05-15 02:44:50'),
	('Beef Stew Meat','Video minime esse deterritum. Quae cum tota res (est) ficta pueriliter, tum ne efficit quidem, quod vult. Nam et laetamur amicorum laetitia aeque atque.','2024-01-03 06:26:15'),
	('Spiralizer','Solida corpora ferri deorsum suo pondere ad.','2024-07-20 02:42:21'),
	('Vanilla Protein Powder','Nam si dicent ab illis has res esse tractatas, ne ipsos quidem Graecos est cur dubitemus dicere et sapientiam propter voluptates expetendam et insipientiam propter molestias esse.','2024-07-01 17:03:42'),
	('Zucchini','Manilium, ab iisque M. Brutus dissentiet -- quod et posse fieri intellegimus et saepe disserui, Latinam linguam non modo non inopem, ut vulgo putarent, sed locupletiorem etiam esse quam Graecam. Quando enim nobis, vel dicam aut.','2024-01-14 02:16:30'),
	('Chocolate Hazelnut Granola','Videor praesidium, in quo admirer, cur in gravissimis.','2024-05-22 16:49:57'),
	('Pork Tenderloin','Metuamus. Iam illud quidem perspicuum est, maximam animi aut voluptatem aut molestiam plus aut ad miseram vitam afferre momenti quam eorum utrumvis, si aeque diu sit in corpore. Non placet autem detracta voluptate aegritudinem statim consequi, nisi in voluptatis locum dolor forte successerit, at contra gaudere nosmet omittendis doloribus, etiamsi voluptas ea, quae dixi, sole ipso.','2024-09-09 01:10:32'),
	('Safety Pin Dispenser','Ea ratio est, ut meminerit maximos morte finiri, parvos multa habere intervalla requietis, mediocrium nos esse dominos, ut, si tolerabiles sint, feramus, si minus.','2024-06-12 10:24:31'),
	('Organic Coconut Flakes','Nihil scilicet novi, ea tamen, quae te ipsum probaturum esse confidam. Certe, inquam, pertinax non ero tibique, si mihi probabis ea, quae dices, libenter assentiar. Probabo, inquit, modo ista sis aequitate, quam ostendis. Sed uti oratione perpetua malo quam interrogare aut interrogari. Ut placet, inquam. Tum dicere exorsus.','2024-03-04 17:45:51'),
	('Cat Scratching Post with Toys','Quam Graecam. Quando enim nobis, vel dicam aut oratoribus bonis aut poetis, postea quidem quam fuit quem imitarentur, ullus orationis vel copiosae vel elegantis ornatus defuit? Ego vero, quoniam forensibus operis, laboribus, periculis non deseruisse mihi videor praesidium, in quo admirer, cur.','2024-08-24 03:13:27'),
	('Pet Caress Brush','Videntur, quibus nostra ignota sunt. An ''Utinam ne in nemore . . .'' nihilo minus legimus quam hoc.','2024-04-22 10:36:55'),
	('Foam Muscle Roller','Mundus omnesque partes mundi, quaeque in eo non arbitrantur. Erunt etiam, et ii quidem eruditi Graecis litteris, contemnentes.','2024-02-12 01:53:16'),
	('Organic Vanilla Bean Ice Cream','Cum a philosophis compluribus permulta dicantur, cur nec voluptas in bonis sit numeranda nec in discordia dominorum domus.','2024-04-26 07:42:58'),
	('Folding Backpack Chair','Eumque errorem et voluptatibus maximis saepe priventur.','2024-11-03 03:46:01'),
	('Portable Electric Fan','Et voluptatem pleniorem efficit. Itaque non ob ea solum incommoda, quae eveniunt inprobis, fugiendam inprobitatem putamus, sed multo magis vita.','2024-02-12 05:43:51'),
	('Customizable Name Plate','Esse quiddam inter dolorem et voluptatem; illud enim ipsum, quod quibusdam medium videretur, cum omni dolore careret, non modo singulos homines, sed universas familias evertunt, totam etiam labefactant saepe rem publicam. Ex cupiditatibus odia, discidia, discordiae, seditiones, bella nascuntur, nec eae se foris solum iactant nec tantum in alios caeco impetu incurrunt, sed intus etiam in.','2024-02-20 05:58:12'),
	('Lemon Herb Grilled Chicken','Perpetua malo quam interrogare aut interrogari. Ut placet, inquam. Tum dicere.','2024-05-10 08:18:20'),
	('Pepperoni Pizza Rolls','Liberalitati magis conveniunt, qua qui est imbutus quietus esse numquam potest. Praeterea.','2024-10-24 08:35:25'),
	('Pet Food Storage Container','Affecta, secundum non recte, si voluptas summum sit bonum, affirmatis nullam omnino fore. De qua Epicurus quidem ita dicit, omnium rerum, quas ad beate.','2024-10-10 22:01:20'),
	('Berries Medley','Eum fugiat, quo voluptas nulla pariatur? At vero eos et accusamus et iusto odio dignissimos ducimus, qui blanditiis praesentium voluptatum deleniti atque corrupti, quos dolores et quas molestias excepturi sint, obcaecati cupiditate.','2024-02-28 11:23:23'),
	('Inflatable Paddle Board','Tibi ita videri necesse est, quid aut ad beatam aut ad bene vivendum aptior partitio quam illa, qua est usus Epicurus? Qui unum genus posuit earum cupiditatum, quae essent et naturales et necessariae, alterum, quae vis sit, quae quidque efficiat, de materia disseruerunt, vim et causam efficiendi reliquerunt. Sed hoc commune vitium.','2024-06-02 07:59:15'),
	('Smoked Paprika','Durissimis animi doloribus torqueantur, sapientia est adhibenda, quae et a falsis initiis profecta vera esse non.','2024-06-17 05:25:52'),
	('Luxury Yoga Mat','Suam. Atque haec ratio late patet. In quo enim maxime consuevit iactare vestra se oratio, tua praesertim, qui studiose antiqua persequeris, claris et fortibus.','2024-02-09 18:08:59'),
	('Stylish Wide-Leg Trousers','Graeca legere malint, modo legant illa ipsa, ne simulent, et iis quidem non admodum indoctis, totum hoc displicet.','2024-03-18 04:54:12'),
	('Chickpea Pancakes','Paratus est, ut Epicuro placet, nihil dolere, primum tibi.','2024-07-06 16:11:17'),
	('Insulated Lunch Bag','Qua quaeque res efficiatur, alterum, quae vis sit, quae quidque efficiat, de materia disseruerunt.','2024-03-02 03:49:49'),
	('Fleece Throw Blanket','Formidines. Denique etiam morati melius erimus, cum didicerimus quid natura desideret. Tum vero, si stabilem.','2024-11-26 02:07:58'),
	('Travel Luggage Scale','Quam nihil molestiae consequatur, vel illum, qui dolorem eum fugiat, quo voluptas nulla pariatur? At vero eos et accusamus et iusto odio.','2024-10-30 03:46:30'),
	('Pet Carrier Backpack','Ut hic noster labor in varias reprehensiones incurreret. Nam quibusdam, et iis quidem non admodum flagitem. Re mihi non aeque satisfacit, et quidem locis.','2024-01-28 02:29:47'),
	('Pest Control Traps','Firme graviterque comprehenderit, ut omnes bene sanos ad iustitiam, aequitatem, fidem, neque homini infanti aut inpotenti iniuste facta conducunt, qui nec facile efficere possit, quod conetur, nec optinere, si effecerit, et opes vel fortunae vel ingenii liberalitati magis conveniunt, qua qui utuntur, benivolentiam sibi conciliant et, quod aptissimum.','2024-04-05 22:11:41'),
	('Solar Charger','Qua maxime ceterorum philosophorum exultat oratio, reperire exitum potest, nisi derigatur ad voluptatem, voluptas.','2024-03-17 08:55:25'),
	('Banana Peanut Butter Smoothie','Divinum numen horreat nec praeteritas voluptates effluere patiatur earumque.','2024-01-06 19:01:32'),
	('Vegan Mac & Cheese','Cum C. Triarius, in primis gravis et doctus adolescens, ei.','2024-09-02 09:02:07'),
	('Puzzle','Nesciunt, neque porro quisquam est, qui alienae modum statuat industriae?','2024-10-22 09:37:31'),
	('Smartphone Photography Tripod','Vita nulla est intercapedo molestiae. Igitur neque stultorum quisquam beatus neque sapientium non beatus. Multoque hoc melius nos veriusque quam Stoici. Illi.','2023-12-07 11:30:21'),
	('Protein Pancake Mix','Ut aliquip ex ea commodo consequat. Duis aute.','2024-04-15 11:30:58'),
	('Memory Foam Pillow','Et non necessariam et quae vel aliter pararetur et qua etiam carere possent sine dolore tum in morbos gravis, tum in morbos gravis, tum in dedecora incurrunt, saepe etiam videmus, et perspicuum est nihil desiderare manum, cum ita esset affecta, secundum non recte, si voluptas summum sit bonum.','2024-11-06 09:30:18'),
	('Cinnamon Raisin Bagels','Praesens et quod quaeritur saepe, cur tam multi sint Epicurei, sunt aliae quoque causae, sed multitudinem haec maxime allicit, quod ita putant dici ab illo, recta et honesta quae sint, ea facere.','2023-12-23 23:00:28'),
	('Whole Grain Cereal','Iis parendum non est. Nihil enim desiderabile concupiscunt, plusque in ipsa iniuria.','2024-07-17 22:37:31'),
	('Falafel Mix','Et parvam et non necessariam et quae fugiamus refert omnia. Quod quamquam Aristippi est a Chrysippo praetermissum in.','2024-06-05 01:35:30'),
	('Honey Roasted Almonds','Enim vestrae eximiae pulchraeque virtutes nisi voluptatem efficerent, quis eas aut laudabilis aut expetendas arbitraretur? Ut enim virtutes, de quibus et ab antiquis, ad arbitrium suum scribere? Quodsi Graeci leguntur a Graecis isdem de rebus alia ratione compositis, quid est, quod huc possit, quod conetur, nec optinere, si effecerit, et opes vel fortunae vel ingenii liberalitati.','2024-06-19 21:51:43'),
	('Yoga Wheel','Desiderat?'' -- Nihil sane. -- ''At, si voluptas esset bonum, fuisse desideraturam. Idcirco.','2024-07-06 20:41:06'),
	('Folding Backpack Chair','Mortis timorem et constantia contra metum religionis et sedatio animi omnium rerum occultarum ignoratione sublata et moderatio natura cupiditatum generibusque earum explicatis, et, ut modo docui, cognitionis.','2024-01-11 13:33:41'),
	('Wall Art','Natura desideret. Tum vero, si stabilem scientiam rerum tenebimus, servata illa, quae quasi saxum Tantalo.','2024-06-19 12:50:14'),
	('Bamboo Toothbrush','Vitam adiuvet, quo facilius id, quod quaeritur, sit pulcherrimum. Etenim si delectamur, cum scribimus, quis est tam invidus, qui ab eo delectari, quod ista Platonis, Aristoteli, Theophrasti.','2024-02-12 04:30:48'),
	('Air Fryer Oven','Ignem, nivem esse albam, dulce mel. Quorum nihil oportere exquisitis rationibus confirmare, tantum satis esse.','2023-12-01 07:55:31'),
	('Garlic Parmesan Roasted Potatoes','Et debilitati obiecta specie voluptatis tradunt se libidinibus constringendos nec quid eventurum.','2024-01-05 04:35:06'),
	('Electric Heating Pad','Plerumque improborum facta primo suspicio insequitur, dein sermo atque fama, tum accusator, tum iudex; Multi etiam, ut te consule, ipsi se indicaverunt. Quodsi qui satis sibi contra.','2024-02-17 02:22:17'),
	('Wireless Charging Station','Dicta sunt ab iis quos probamus, eisque nostrum iudicium et nostrum scribendi.','2024-04-11 03:03:46'),
	('Peanut Butter Cookies','De omni virtute sit dictum. Sed similia fere dici possunt. Ut enim.','2024-10-07 04:13:07'),
	('Cold Brew Coffee Maker','Officia deserunt mollit anim id est voluptatem et dolorem? Sunt autem.','2024-05-02 23:22:45'),
	('Handcrafted Wooden Jewelry Box','Et verborum vis et natura orationis et consequentium repugnantiumve ratio potest perspici. Omnium autem.','2024-05-10 20:05:26'),
	('Mini Projector','Rebus expetendis, quid fugiat ut extremum malorum? Qua de re cum sit inter doctissimos summa dissensio, quis alienum putet eius esse dignitatis, quam mihi quisque tribuat.','2024-08-31 23:41:48'),
	('Plant-Based Cookbook','Labores magnosque susceperant. Ecce autem alii minuti et angusti aut omnia semper desperantes aut malivoli, invidi, difficiles, lucifugi, maledici, monstruosi.','2024-02-10 20:51:47'),
	('Almond Flour Pizza Crust','Et consequentium repugnantiumve ratio potest perspici. Omnium autem rerum natura cognita levamur superstitione, liberamur mortis metu, non conturbamur ignoratione rerum, e qua ipsa horribiles.','2024-05-09 14:05:23'),
	('Smartphone Car Mount with Wireless Charging','Quam gravis, quam continens, quam severa sit. Non enim hanc solam sequimur, quae suavitate aliqua naturam ipsam movet.','2023-12-17 11:00:09'),
	('Wrist Support Brace','Ad alias litteras vocent, genus hoc scribendi, etsi sit elegans, personae tamen et dignitatis esse negent. Contra quos omnis dicendum breviter existimo. Quamquam philosophiae quidem vituperatoribus satis responsum.','2024-04-18 05:07:20'),
	('Chicken Fajita Kit','Philosophi Graeco sermone tractavissent, ea Latinis litteris mandaremus, fore ut.','2024-08-02 00:07:35'),
	('Smartphone Gimbal Stabilizer','Esse, ut ad Orestem pervenias profectus a Theseo. At vero Epicurus una in domo, et ea quidem angusta, quam magnos quantaque amoris conspiratione consentientis tenuit amicorum greges! Quod.','2024-05-07 15:38:21');


-- Populate project table
INSERT INTO project (project_id, availability, project_creation_date, archived_status, updated_at, project_title, project_description)
VALUES
    (1,FALSE,'2024-11-12 16:02:32',FALSE,'2024-11-12 16:02:32','Printed Maxi Skirt','A colorful printed maxi skirt for a bohemian look.'),
	(2,TRUE,'2024-06-05 04:56:41',TRUE,'2024-06-05 04:56:41','Cajun Seasoning','remote Spicy seasoning mix for all your favorite dishes.'),
	(3,FALSE,'2024-03-15 22:00:39',FALSE,'2024-03-15 22:00:39','Instant Camera','Retro instant camera for capturing and printing photos instantly.'),
	(4,TRUE,'2024-03-14 01:08:46',TRUE,'2024-03-14 01:08:46','Wireless Car Charger','Convenient charging pad for wireless charging in vehicles.'),
	(5,TRUE,'2024-02-18 14:52:22',TRUE,'2024-02-18 14:52:22','Honey Mustard Chicken Breasts','Marinated chicken breasts coated in a sweet honey mustard glaze.'),
	(6,FALSE,'2024-04-17 23:29:20',FALSE,'2024-04-17 23:29:20','Reusable Food Storage Bags','Eco-friendly silicone bags for food storage and snacks.'),
	(7,TRUE,'2024-07-17 13:50:20',TRUE,'2024-07-17 13:50:20','LED Strip Lights with Remote Control','Color-changing LED lights for home decoration with remote.'),
	(8,FALSE,'2024-05-01 19:36:16',FALSE,'2024-05-01 19:36:16','Honey Wheat Pretzels','Crunchy pretzel sticks made with honey and whole wheat.'),
	(9,FALSE,'2024-01-18 16:04:02',FALSE,'2024-01-18 16:04:02','Portable Leaf Blower','Lightweight leaf blower for maintaining outdoor spaces.'),
	(10,FALSE,'2023-12-07 19:33:22',FALSE,'2023-12-07 19:33:22','Garden Kneeler and Seat','Convertible kneeler and seat for gardening comfort.'),
	(11,TRUE,'2024-07-05 11:16:25',TRUE,'2024-07-05 11:16:25','Sketchbook','High-quality sketchbook for artists.'),
	(12,FALSE,'2024-04-02 15:09:26',FALSE,'2024-04-02 15:09:26','Hot Salsa','Spicy salsa made with fresh ingredients.'),
	(13,FALSE,'2024-02-22 14:31:12',FALSE,'2024-02-22 14:31:12','Body Pillow Case','Soft and breathable pillowcase for body pillows.'),
	(14,FALSE,'2024-04-29 09:46:58',FALSE,'2024-04-29 09:46:58','Electric Ice Cream Maker','Make delicious ice cream at home with this user-friendly machine.'),
	(15,TRUE,'2024-03-11 22:16:35',TRUE,'2024-03-11 22:16:35','Pet Safety Harness','Comfortable harness designed to keep pets safe in the car.'),
	(16,FALSE,'2024-06-14 11:54:31',FALSE,'2024-06-14 11:54:31','Blue Corn Tortilla Chips','Crunchy chips made from blue corn, perfect for dipping.'),
	(17,FALSE,'2024-04-03 18:12:48',FALSE,'2024-04-03 18:12:48','Kids'' Learning Tablet','Educational tablet designed for preschool-age children.'),
	(18,FALSE,'2024-03-17 11:23:39',FALSE,'2024-03-17 11:23:39','Electric Air Pump','Fast and convenient air pump for inflating toys and furniture.'),
	(19,FALSE,'2024-08-27 18:07:09',FALSE,'2024-08-27 18:07:09','Sweet and Spicy Barbecue Sauce','A flavorful barbecue sauce with a sweet and spicy kick.'),
	(20,FALSE,'2024-11-01 17:35:26',FALSE,'2024-11-01 17:35:26','Spinach and Ricotta Ravioli','Delicious ravioli filled with creamy ricotta and fresh spinach.'),
	(21,TRUE,'2024-11-01 11:41:13',TRUE,'2024-11-01 11:41:13','Pumpkin Spice Muffins','Moist and flavorful muffins packed with fall spices and pumpkin puree.'),
	(22,FALSE,'2024-09-18 23:31:42',FALSE,'2024-09-18 23:31:42','Italian Herbal Seasoning','A fragrant blend of Italian herbs for pasta sauces and marinades.'),
	(23,FALSE,'2024-11-13 22:50:25',FALSE,'2024-11-13 22:50:25','Ribbed Knit Dress','A fitted ribbed knit dress that hugs your curves perfectly.'),
	(24,FALSE,'2024-06-26 11:52:33',FALSE,'2024-06-26 11:52:33','Dill Pickle Chips','Crispy dill pickles that are perfect for snacking or sandwiches.'),
	(25,FALSE,'2024-03-06 00:03:58',FALSE,'2024-03-06 00:03:58','High-Quality Yoga Block','Foam yoga block for enhancing poses and stability.'),
	(26,FALSE,'2024-08-03 08:28:27',FALSE,'2024-08-03 08:28:27','Sriracha Chili Sauce','Spicy chili sauce with garlic and sugar for a flavor kick.'),
	(27,TRUE,'2024-02-22 18:34:19',TRUE,'2024-02-22 18:34:19','Glass Food Containers','BPA-free glass containers for safe food storage.'),
	(28,FALSE,'2024-06-16 15:30:20',FALSE,'2024-06-16 15:30:20','Black Bean Spaghetti','High-protein pasta made from black beans, gluten-free.'),
	(29,FALSE,'2024-06-20 23:06:02',FALSE,'2024-06-20 23:06:02','Hummus Variety Pack','A selection of different flavored hummus, great for snacking.'),
	(30,FALSE,'2024-10-20 22:33:25',FALSE,'2024-10-20 22:33:25','Lentil Pasta','Gluten-free pasta made from lentils, high in protein.'),
	(31,FALSE,'2024-07-22 18:49:59',FALSE,'2024-07-22 18:49:59','LED Flashlight','Bright LED flashlight with adjustable beam.'),
	(32,TRUE,'2023-12-29 12:57:03',TRUE,'2023-12-29 12:57:03','Basmati Rice','Aromatic long-grain basmati rice, perfect for curries.'),
	(33,TRUE,'2024-05-25 00:28:45',TRUE,'2024-05-25 00:28:45','Chickpea Salad Deluxe','Chickpeas mixed with fresh vegetables and herbs, a nutritious snack or salad.'),
	(34,TRUE,'2024-10-18 18:30:11',TRUE,'2024-10-18 18:30:11','Dog Training Whistle','High-frequency whistle for training your dog effectively.'),
	(35,TRUE,'2024-03-02 20:17:04',TRUE,'2024-03-02 20:17:04','Smart LED Desk Lamp','Adjustable lamp with multiple brightness levels and colors.'),
	(36,FALSE,'2024-04-01 13:20:22',FALSE,'2024-04-01 13:20:22','Portable Pet Water Bottle','Travel-friendly water bottle for pets on the go.'),
	(37,TRUE,'2024-03-14 07:47:17',TRUE,'2024-03-14 07:47:17','Basil Pesto Pasta','Pasta tossed with fresh basil pesto, simple and delicious.'),
	(38,FALSE,'2024-08-06 00:25:26',FALSE,'2024-08-06 00:25:26','Frozen Berry Blend','A mix of frozen berries for smoothies or desserts'),
	(39,TRUE,'2024-08-13 17:51:41',TRUE,'2024-08-13 17:51:41','Veggie Burger Patties','Delicious veggie burger patties for grilling or frying.'),
	(40,FALSE,'2024-08-05 00:37:38',FALSE,'2024-08-05 00:37:38','Car Diagnostic Scanner','Tool to check car engine codes and performance issues.'),
	(41,FALSE,'2024-04-21 07:14:37',FALSE,'2024-04-21 07:14:37','Satin Slip Dress','Luxurious satin slip dress for an elegant evening look.'),
	(42,FALSE,'2024-11-15 07:20:56',FALSE,'2024-11-15 07:20:56','Coconut Granola','Crunchy granola mixed with coconut flakes.'),
	(43,FALSE,'2024-07-05 13:50:21',FALSE,'2024-07-05 13:50:21','Cotton Basic Tank','Essential cotton tank top, perfect for layering.'),
	(44,TRUE,'2024-11-17 15:34:40',TRUE,'2024-11-17 15:34:40','Jasmine Rice','Fragrant jasmine rice, perfect as a side dish.'),
	(45,TRUE,'2024-06-16 12:18:57',TRUE,'2024-06-16 12:18:57','Fitness Activity Journal','Journal to record workouts and nutrition.'),
	(46,TRUE,'2024-01-30 22:19:24',TRUE,'2024-01-30 22:19:24','Herbed Goat Cheese','Tangy goat cheese infused with herbs, perfect for snacking.'),
	(47,FALSE,'2024-07-15 20:42:07',FALSE,'2024-07-15 20:42:07','Gingerbread House Kit','Everything you need to build a festive gingerbread house.'),
	(48,TRUE,'2024-08-25 17:16:30',TRUE,'2024-08-25 17:16:30','Creamy Ranch Dressing','Classic ranch dressing for salads and dipping.'),
	(49,TRUE,'2024-07-31 06:16:43',TRUE,'2024-07-31 06:16:43','Herbed Goat Cheese','Tangy goat cheese infused with herbs, perfect for snacking.'),
	(50,TRUE,'2024-07-15 19:56:27',TRUE,'2024-07-15 19:56:27','Fried Rice','Pre-cooked vegetable fried rice, just heat and serve.'),
	(51,FALSE,'2024-06-03 23:57:18',FALSE,'2024-06-03 23:57:18','Solar Garden Lights','Energy-efficient lights that charge during the day and illuminate at night.'),
	(52,TRUE,'2024-02-19 15:37:23',TRUE,'2024-02-19 15:37:23','Silicone Baking Mat Set','Non-stick and reusable mats for easy baking.'),
	(53,TRUE,'2024-05-10 15:48:36',TRUE,'2024-05-10 15:48:36','Portable Air Purifier','Compact air purifier to improve indoor air quality.'),
	(54,TRUE,'2024-02-29 22:07:26',TRUE,'2024-02-29 22:07:26','Plant Watering Spikes','Automatic watering devices for potted plants.'),
	(55,TRUE,'2024-03-26 17:17:14',TRUE,'2024-03-26 17:17:14','Active Racerback Tank','Lightweight and moisture-wicking racerback tank for workouts.'),
	(56,TRUE,'2024-11-23 17:24:31',TRUE,'2024-11-23 17:24:31','Oven-Baked Chicken Tenders','Crispy and juicy chicken tenders, perfect for dipping.'),
	(57,FALSE,'2024-09-06 08:13:52',FALSE,'2024-09-06 08:13:52','Hiking Gaiters','Protective gaiters to keep dirt and debris out of shoes during hikes.'),
	(58,FALSE,'2024-03-20 13:42:08',FALSE,'2024-03-20 13:42:08','Energy Bites','Healthy energy bites made with oats and natural sweeteners.'),
	(59,TRUE,'2024-07-16 03:20:51',TRUE,'2024-07-16 03:20:51','Classic Baseball Cap','A timeless baseball cap that adds a sporty touch to any outfit.'),
	(60,TRUE,'2024-11-19 02:13:37',TRUE,'2024-11-19 02:13:37','Orange Ginger Vinaigrette','Tangy vinaigrette with orange and ginger flavors.'),
	(61,FALSE,'2024-10-27 00:39:55',FALSE,'2024-10-27 00:39:55','Kid''s Fruit Snacks','Assorted fruit-flavored gummy snacks that kids love.'),
	(62,TRUE,'2024-06-25 09:40:55',TRUE,'2024-06-25 09:40:55','Black Bean Spaghetti','High-protein pasta made from black beans, gluten-free.'),
	(63,FALSE,'2024-05-02 12:29:39',FALSE,'2024-05-02 12:29:39','Apple Cinnamon Granola','A wholesome granola with bits of apple and a touch of cinnamon.'),
	(64,FALSE,'2024-06-21 00:09:27',FALSE,'2024-06-21 00:09:27','Frozen Cauliflower Rice','Convenient and low-carb alternative to traditional rice.'),
	(65,TRUE,'2024-06-24 19:00:00',TRUE,'2024-06-24 19:00:00','Smart Wi-Fi Light Bulbs','Energy-efficient LED bulbs that can be controlled via smartphone.'),
	(66,TRUE,'2024-03-16 11:04:09',TRUE,'2024-03-16 11:04:09','Wireless Charger Stand','Convenient charging stand for smartphones and devices.'),
	(67,TRUE,'2024-02-25 02:18:07',TRUE,'2024-02-25 02:18:07','Classic Minestrone Soup','Hearty minestrone soup loaded with vegetables and pasta.'),
	(68,TRUE,'2024-11-05 09:36:24',TRUE,'2024-11-05 09:36:24','Water Bottle','Insulated water bottle for keeping drinks cold.'),
	(69,TRUE,'2023-12-04 12:12:28',TRUE,'2023-12-04 12:12:28','Travel Hair Straightener','Compact hair straightener for travel.'),
	(70,FALSE,'2023-12-24 22:56:32',FALSE,'2023-12-24 22:56:32','Collapsible Camping Cup','Space-saving cup that folds flat for easy storage.'),
	(71,FALSE,'2024-03-12 05:09:41',FALSE,'2024-03-12 05:09:41','Animal Paw Print Soap Dispenser','Cute dispenser for bathrooms or kitchens featuring paw prints.'),
	(72,TRUE,'2024-03-19 22:23:19',TRUE,'2024-03-19 22:23:19','Stainless Steel Mixing Bowls','Set of versatile mixing bowls for cooking.'),
	(73,FALSE,'2023-12-29 01:56:26',FALSE,'2023-12-29 01:56:26','Creamy Avocado Dip','Rich and creamy dip made with real avocado, great for chips.'),
	(74,TRUE,'2024-08-18 10:00:47',TRUE,'2024-08-18 10:00:47','Fashionable Scarves Set','Stylish scarves to accessorize any outfit.'),
	(75,TRUE,'2024-01-29 05:55:48',TRUE,'2024-01-29 05:55:48','Puff Pastry','Versatile puff pastry for pies and pastries.'),
	(76,FALSE,'2024-06-07 21:04:19',FALSE,'2024-06-07 21:04:19','Siphon Coffee Maker','Unique coffee brewing method for a flavorful experience.'),
	(77,TRUE,'2023-11-30 06:14:38',TRUE,'2023-11-30 06:14:38','Chickpea Salad','A ready-to-eat salad made with chickpeas and veggies.'),
	(78,FALSE,'2024-03-30 11:13:38',FALSE,'2024-03-30 11:13:38','Kale Salad Kit','Ready-to-eat salad with kale, lemon, and cheese.'),
	(79,TRUE,'2024-07-20 02:23:14',TRUE,'2024-07-20 02:23:14','Adjustable Skipping Rope','Durable skipping rope with adjustable length for workouts.'),
	(80,FALSE,'2024-07-26 08:04:45',FALSE,'2024-07-26 08:04:45','Legging Pants','Comfortable and stretchy legging pants perfect for workouts or daily wear.'),
	(81,FALSE,'2024-03-12 21:43:50',FALSE,'2024-03-12 21:43:50','Homestyle Chicken Noodle Soup','Classic chicken noodle soup with tender chicken and vegetables.'),
	(82,FALSE,'2024-11-10 05:36:05',FALSE,'2024-11-10 05:36:05','Vegan chocolate chip cookies','Delicious soft cookies, dairy-free and egg-free, perfect for treats.'),
	(83,TRUE,'2024-08-30 19:45:03',TRUE,'2024-08-30 19:45:03','Wireless Charging Station','Charge multiple devices with this sleek charging station.'),
	(84,FALSE,'2024-06-21 06:06:31',FALSE,'2024-06-21 06:06:31','Heated Throw Blanket','Soft blanket that provides warmth with adjustable settings.'),
	(85,TRUE,'2024-05-10 21:44:12',TRUE,'2024-05-10 21:44:12','Action Camera','Compact action camera for capturing adventures.'),
	(86,FALSE,'2024-04-19 18:26:54',FALSE,'2024-04-19 18:26:54','Reusable Coffee Filter','Eco-friendly coffee filter for brewing.'),
	(87,FALSE,'2023-12-28 12:57:12',FALSE,'2023-12-28 12:57:12','Window Bird Feeder with Suction Cups','Clear feeder that attaches to windows for bird watching.'),
	(88,TRUE,'2024-01-18 21:52:50',TRUE,'2024-01-18 21:52:50','Bamboo Cutting Board','Eco-friendly bamboo cutting board for food prep.'),
	(89,TRUE,'2024-03-21 04:19:05',TRUE,'2024-03-21 04:19:05','Tomato Basil Soup','A classic soup combining tomatoes and basil, great with grilled cheese sandwiches.'),
	(90,FALSE,'2023-12-03 17:47:48',FALSE,'2023-12-03 17:47:48','Apple Pie Filling','Sweet and spiced apple filling, perfect for pies.'),
	(91,TRUE,'2024-10-15 12:19:14',TRUE,'2024-10-15 12:19:14','Waffle Maker','Make delicious waffles with this user-friendly device.'),
	(92,TRUE,'2024-08-22 14:47:43',TRUE,'2024-08-22 14:47:43','Bluetooth Shower Speaker','Water-resistant Bluetooth speaker for showers.'),
	(93,TRUE,'2024-04-19 22:39:27',TRUE,'2024-04-19 22:39:27','Outdoor Camping Hammock','Lightweight and durable hammock for relaxing in nature.'),
	(94,FALSE,'2023-12-28 07:36:39',FALSE,'2023-12-28 07:36:39','Biodegradable Dog Waste Bags','Eco-friendly bags for picking up after your pet.'),
	(95,TRUE,'2023-11-30 15:35:04',TRUE,'2023-11-30 15:35:04','Portable Folding Picnic Table','Easy-to-set-up picnic table for outdoor dining.'),
	(96,FALSE,'2024-02-16 19:24:36',FALSE,'2024-02-16 19:24:36','Aged White Cheddar Popcorn','Light and fluffy popcorn coated in aged white cheddar.'),
	(97,FALSE,'2024-03-24 00:23:09',FALSE,'2024-03-24 00:23:09','Savory Oatmeal Cups','Savory oatmeal ready to eat, great for breakfast or a snack.'),
	(98,TRUE,'2024-04-02 23:51:44',TRUE,'2024-04-02 23:51:44','Fruit Infuser Water Bottle','Water bottle designed to infuse flavors from fruits.'),
	(99,TRUE,'2024-09-20 04:25:33',TRUE,'2024-09-20 04:25:33','Trackpad for Laptop','Wireless trackpad for enhanced laptop navigation.'),
	(100,TRUE,'2024-05-11 01:20:57',TRUE,'2024-05-11 01:20:57','Smartphone Tripod with Remote','Adjustable tripod with remote shutter for smartphones.'),
	(101,TRUE,'2024-12-01 10:15:45',FALSE,'2024-12-01 10:15:45','Open Source Calendar','A collaborative open source calendar for team scheduling.'),
    (102,FALSE,'2024-11-20 08:30:12',TRUE,'2024-11-20 08:30:12','Charity Remote Drive App','An app designed to organize and track charity drives.'),
    (103,TRUE,'2024-11-18 14:45:22',FALSE,'2024-11-18 14:45:22','Portable Solar Panel','Compact and efficient solar panels for on-the-go power.'),
    (104,TRUE,'2024-11-15 09:10:33',FALSE,'2024-11-15 09:10:33','Open Source Task Manager','A user-friendly task manager built for open source projects.'),
    (105,TRUE,'2024-11-10 17:25:49',TRUE,'2024-11-10 17:25:49','remote Charity Cookbook','A cookbook with recipes contributed by charity volunteers.'),
    (106,FALSE,'2024-11-08 11:40:05',FALSE,'2024-11-08 11:40:05','Eco-friendly Backpack','A durable and eco-friendly backpack made from recycled materials.'),
    (107,TRUE,'2024-11-06 13:55:18',FALSE,'2024-11-06 13:55:18','Charity Auction Platform','An online platform for organizing charity auctions.'),
    (108,TRUE,'2024-11-04 16:20:47',TRUE,'2024-11-04 16:20:47','Open Source Analytics Tool','A data analytics tool for open source enthusiasts.'),
    (109,TRUE,'2024-11-02 07:35:29',FALSE,'2024-11-02 07:35:29','Wireless Earbuds','High-quality wireless earbuds with noise-canceling features.'),
    (110,FALSE,'2024-11-01 12:45:01',TRUE,'2024-11-01 12:45:01','Charity Concert Organizer','Software to manage and promote charity concerts.'),
	(111,TRUE,'2024-12-10 14:12:45',FALSE,'2024-12-10 14:12:45','Volunteer Matching Platform','An open source platform looking for people to join its development team.'),
    (112,TRUE,'2024-12-08 09:25:30',TRUE,'2024-12-08 09:25:30','Charity Fund Tracker','An open source system designed to help charities manage funds, looking for people to beta test.'),
    (113,TRUE,'2024-12-06 16:40:10',FALSE,'2024-12-06 16:40:10','Recipe Database','A collaborative recipe database looking for people to contribute new ideas.'),
    (114,FALSE,'2024-12-04 11:05:50',TRUE,'2024-12-04 11:05:50','Eco-Friendly Cleaning Supplies','A charity-driven project promoting sustainability, looking for people to help distribute.'),
    (115,TRUE,'2024-12-02 10:15:25',FALSE,'2024-12-02 10:15:25','Remote Work Organizer','A productivity app for remote teams that is open source and looking for people to provide feedback.'),
    (116,FALSE,'2024-11-30 17:30:15',TRUE,'2024-11-30 17:30:15','Charity Event Scheduler','A tool to help plan charity events, actively looking for people to test features.'),
    (117,TRUE,'2024-11-28 08:45:35',FALSE,'2024-11-28 08:45:35','Community Forum Software','An open source forum platform looking for people to develop plugins.'),
    (118,FALSE,'2024-11-26 13:22:10',TRUE,'2024-11-26 13:22:10','Accessible Education Program','A charity-focused educational program looking for people to volunteer as mentors.'),
    (119,TRUE,'2024-11-24 15:50:40',FALSE,'2024-11-24 15:50:40','Open Source Graphic Design Tool','A free graphic design tool, looking for people to translate documentation.'),
    (120,FALSE,'2024-11-22 12:10:20',TRUE,'2024-11-22 12:10:20','Charity Music Initiative','A charity initiative for music education, looking for people to organize workshops.');


-- Populate task table
INSERT INTO task (task_id,project_id, task_name, status, details, due_date, priority, created_at, updated_at)
VALUES
    (1,44,'Mini Projector for Smartphones','Ongoing','Prepare presentation slides','2026-05-02 09:30:47','Low','2023-12-16 04:56:06','2023-12-16 04:56:06'),
	(2,6,'Portable Speakers','On-hold','Attend team meeting','2025-12-15 09:50:07','High','2024-05-22 14:45:56','2024-05-22 14:45:56'),
	(3,49,'Zucchini','Ongoing','Update the project timeline','2026-05-22 19:13:59','Low','2024-07-05 13:04:56','2024-07-05 13:04:56'),
	(4,61,'Spicy Tuna Rolls','Finished','Plan the marketing strategy','2026-07-05 17:25:47','Medium','2024-08-03 22:02:00','2024-08-03 22:02:00'),
	(5,69,'Creative Puzzle Game','Ongoing','Coordinate supplier contracts','2026-08-04 02:18:02','Low','2024-04-21 20:22:03','2024-04-21 20:22:03'),
	(6,41,'Californian Raisins','Ongoing','Prepare presentation slides','2026-04-22 00:55:09','Low','2024-06-16 10:30:07','2024-06-16 10:30:07'),
	(7,56,'Fashionable Scarves Set','On-hold','Update the project timeline','2026-06-16 14:54:06','High','2024-01-29 09:50:20','2024-01-29 09:50:20'),
	(8,18,'Silicone Stretch Lids','On-hold','Attend team meeting','2026-01-28 14:37:06','High','2024-03-15 11:51:03','2024-03-15 11:51:03'),
	(9,9,'Portable Air Conditioner','On-hold','Plan the marketing strategy','2026-03-15 16:30:16','High','2023-12-27 08:38:34','2023-12-27 08:38:34'),
	(10,11,'Canned Coconut Milk','On-hold','Submit the financial report','2025-12-26 13:30:45','High','2024-06-29 00:11:13','2024-06-29 00:11:13'),
	(11,78,'Children''s Art Set','Finished','Test the new software features','2026-06-29 04:33:08','Medium','2024-01-04 20:13:41','2024-01-04 20:13:41'),
	(12,38,'Wrist Support Brace','Ongoing','Complete the project proposal','2026-01-04 01:04:29','Low','2024-02-22 04:26:28','2024-02-22 04:26:28'),
	(13,60,'Natural Soy Candles','Ongoing','Attend team meeting','2026-02-21 09:09:20','Low','2024-03-08 06:24:11','2024-03-08 06:24:11'),
	(14,49,'Mini Air Purifier','On-hold','Attend team meeting','2026-03-08 11:04:35','High','2024-09-05 10:34:52','2024-09-05 10:34:52'),
	(15,8,'Almond Flour','On-hold','Draft the newsletter content','2026-09-05 14:45:34','High','2024-04-13 00:37:29','2024-04-13 00:37:29'),
	(16,26,'Ice Cream Maker','Ongoing','Coordinate supplier contracts','2026-04-13 05:12:01','Low','2024-06-30 17:18:08','2024-06-30 17:18:08'),
	(17,14,'Plant-Based Protein Bars','Finished','Review the codebase','2026-06-30 21:39:46','Medium','2024-05-20 19:21:30','2024-05-20 19:21:30'),
	(18,50,'Self-Adhesive Wallpaper','On-hold','Organize the team building event','2026-05-20 23:49:51','High','2023-12-22 10:37:51','2023-12-22 10:37:51'),
	(19,31,'Whole Grain Mustard','On-hold','Draft the newsletter content','2025-12-21 15:30:51','High','2024-02-26 11:19:51','2024-02-26 11:19:51'),
	(20,93,'Pest Control Traps','On-hold','Design the new website layout','2026-02-25 16:02:02','High','2024-01-14 06:17:41','2024-01-14 06:17:41'),
	(21,90,'Lentil Soup','Finished','Design the new website layout','2026-01-13 11:06:56','Medium','2024-05-25 04:57:32','2024-05-25 04:57:32'),
	(22,3,'Fire Roasted Salsa','On-hold','Coordinate supplier contracts','2026-05-25 09:25:10','High','2024-03-17 06:46:24','2024-03-17 06:46:24'),
	(23,9,'Wireless Security Camera','Finished','Follow up with the client','2026-03-17 11:25:19','Medium','2024-10-30 21:45:46','2024-10-30 21:45:46'),
	(24,29,'Dish Soap Dispenser','Ongoing','Prepare the quarterly budget','2026-10-31 01:47:23','Low','2024-10-19 03:15:10','2024-10-19 03:15:10'),
	(25,65,'Coffee Grinder','Finished','Submit the financial report','2026-10-19 07:18:42','Medium','2023-12-06 22:28:35','2023-12-06 22:28:35'),
	(26,77,'Ultraviolet Phone Sanitizer','Finished','Review the codebase','2025-12-06 03:24:07','Medium','2023-12-27 16:20:05','2023-12-27 16:20:05'),
	(27,22,'Hibiscus Tea Bags','On-hold','Complete the project proposal','2025-12-26 21:12:14','High','2023-12-21 21:47:37','2023-12-21 21:47:37'),
	(28,74,'Kettle Chip Variety Pack','On-hold','Test the new software features','2025-12-21 02:40:42','High','2024-03-09 17:54:53','2024-03-09 17:54:53'),
	(29,35,'Cinnamon Ice Cream','Finished','Update the project timeline','2026-03-09 22:35:03','Medium','2024-07-19 07:20:24','2024-07-19 07:20:24'),
	(30,80,'Set of Silicone Baking Molds','Finished','Arrange the customer feedback session','2026-08-31 06:07:19','Medium','2024-08-31 01:55:44','2024-08-31 01:55:44'),
	(31,87,'Organic Lentil Soup','Finished','Arrange the customer feedback session','2026-02-13 10:25:40','Medium','2024-02-14 05:41:30','2024-02-14 05:41:30'),
	(32,31,'Pesto Pasta Salad','Ongoing','Conduct performance reviews','2026-08-20 04:44:01','Low','2024-08-20 00:30:38','2024-08-20 00:30:38'),
	(33,30,'Sustainable Wooden Toys','Ongoing','Draft the newsletter content','2026-03-31 10:38:28','Low','2024-03-31 06:01:50','2024-03-31 06:01:50'),
	(34,98,'Adjustable Skipping Rope','Finished','Complete the project proposal','2026-09-13 04:37:11','Medium','2024-09-13 00:27:43','2024-09-13 00:27:43'),
	(35,6,'Instant Mashed Potatoes','Ongoing','Attend team meeting','2026-07-14 09:40:07','Low','2024-07-14 05:20:42','2024-07-14 05:20:42'),
	(36,4,'Sporty Cap','On-hold','Review the codebase','2026-10-09 12:52:10','High','2024-10-09 08:47:02','2024-10-09 08:47:02'),
	(37,82,'Portable Hammock','Finished','Prepare presentation slides','2026-03-16 07:55:00','Medium','2024-03-16 03:15:53','2024-03-16 03:15:53'),
	(38,84,'Smart Thermostat','Ongoing','Plan the marketing strategy','2026-03-13 00:37:26','Low','2024-03-12 19:57:47','2024-03-12 19:57:47'),
	(39,98,'Cranberry Lime Sparkling Water','On-hold','Update the project timeline','2026-07-03 00:22:07','High','2024-07-02 20:00:49','2024-07-02 20:00:49'),
	(40,57,'Peanut Butter Banana Smoothie','Finished','Attend team meeting','2026-11-17 07:08:01','Medium','2024-11-17 03:09:14','2024-11-17 03:09:14'),
	(41,41,'Cotton Basic Tank','On-hold','Conduct performance reviews','2025-12-15 12:16:48','High','2023-12-16 07:22:48','2023-12-16 07:22:48'),
	(42,52,'Miso Soup Mix','On-hold','Draft the newsletter content','2025-12-08 01:02:49','High','2023-12-08 20:07:35','2023-12-08 20:07:35'),
	(43,70,'LED Flashlight with Rechargeable Batteries','On-hold','Submit the financial report','2026-09-19 10:29:26','High','2024-09-19 06:21:00','2024-09-19 06:21:00'),
	(44,16,'Chocolate Mint Protein Shake','On-hold','Submit the financial report','2026-09-26 23:08:57','High','2024-09-26 19:01:45','2024-09-26 19:01:45'),
	(45,79,'Wireless Earbud Silicone Covers','Ongoing','Test the new software features','2026-08-31 00:39:14','Low','2024-08-30 20:27:37','2024-08-30 20:27:37'),
	(46,20,'Organic Fruit Salad','Finished','Draft the newsletter content','2026-11-17 20:28:18','Medium','2024-11-17 16:29:37','2024-11-17 16:29:37'),
	(47,62,'Sun-Dried Tomatoes','Finished','Test the new software features','2026-06-19 02:25:27','Medium','2024-06-18 22:01:52','2024-06-18 22:01:52'),
	(48,5,'Non-Stick Crepe Pan','Finished','Update the project timeline','2026-04-23 17:23:39','Medium','2024-04-23 12:50:50','2024-04-23 12:50:50'),
	(49,3,'Portable Dog Water Bottle','On-hold','Arrange the customer feedback session','2026-10-03 00:38:59','High','2024-10-02 20:32:47','2024-10-02 20:32:47'),
	(50,41,'Grilled Veggie Burgers','Finished','Organize the team building event','2026-06-01 05:15:22','Medium','2024-06-01 00:48:52','2024-06-01 00:48:52'),
	(51,69,'Arcade Game Machine','On-hold','Update the project timeline','2026-01-26 22:49:11','High','2024-01-27 18:02:09','2024-01-27 18:02:09'),
	(52,73,'Pecan Nuts','Ongoing','Prepare presentation slides','2026-08-07 09:11:19','Low','2024-08-07 04:55:50','2024-08-07 04:55:50'),
	(53,85,'Pet Water Bottle','On-hold','Test the new software features','2026-07-03 02:51:53','High','2024-07-02 22:30:36','2024-07-02 22:30:36'),
	(54,25,'Raspberry Tart','On-hold','Follow up with the client','2026-04-24 18:10:07','High','2024-04-24 13:37:28','2024-04-24 13:37:28'),
	(55,90,'Wireless Earbuds','Ongoing','Organize the team building event','2026-01-19 23:38:30','Low','2024-01-20 18:50:19','2024-01-20 18:50:19'),
	(56,60,'Beard Grooming Kit','On-hold','Coordinate supplier contracts','2026-09-09 14:03:01','High','2024-09-09 09:52:58','2024-09-09 09:52:58'),
	(57,95,'Coconut Curry Sauce','Ongoing','Design the new website layout','2026-02-05 19:23:40','Low','2024-02-06 14:38:14','2024-02-06 14:38:14'),
	(58,45,'Mint Chocolate Chip Ice Cream','On-hold','Coordinate supplier contracts','2026-07-07 14:42:59','High','2024-07-07 10:22:26','2024-07-07 10:22:26'),
	(59,65,'Cranberry Almond Biscotti','On-hold','Update the project timeline','2025-12-12 18:24:19','High','2023-12-13 13:29:52','2023-12-13 13:29:52'),
	(60,3,'Multi-Functional Rice Cooker','Ongoing','Design the new website layout','2026-01-01 02:27:22','Low','2024-01-01 21:36:05','2024-01-01 21:36:05'),
	(61,9,'Honey mustard chicken tenders','Finished','Review the codebase','2026-10-27 21:11:35','Medium','2024-10-27 17:09:27','2024-10-27 17:09:27'),
	(62,37,'Dog Training Collar','Ongoing','Prepare presentation slides','2025-12-03 09:48:29','Low','2023-12-04 04:52:30','2023-12-04 04:52:30'),
	(63,40,'Vegetarian Stir Fry Sauce','On-hold','Complete the project proposal','2026-04-23 21:39:04','High','2024-04-23 17:06:17','2024-04-23 17:06:17'),
	(64,25,'Floral Summer Dress','On-hold','Complete the project proposal','2026-08-03 03:09:16','High','2024-08-02 22:53:05','2024-08-02 22:53:05'),
	(65,60,'Coconut Cream Pie Yogurt','Finished','Submit the financial report','2026-08-17 13:35:57','Medium','2024-08-17 09:22:08','2024-08-17 09:22:08'),
	(66,9,'Children''s Science Experiment Lab Kit','Finished','Prepare presentation slides','2026-09-30 18:48:12','Medium','2024-09-30 14:41:37','2024-09-30 14:41:37'),
	(67,47,'Organic Cucumber','Ongoing','Attend team meeting','2026-10-17 21:44:12','Low','2024-02-23 06:15:59','2024-02-23 06:15:59'),
	(68,66,'Wireless Earbuds','Finished','Plan the marketing strategy','2026-01-01 00:51:21','Medium','2024-10-17 17:40:26','2024-10-17 17:40:26'),
	(69,92,'Bird Feeder','Ongoing','Design the new website layout','2026-07-01 01:48:19','Low','2024-01-01 20:00:03','2024-01-01 20:00:03'),
	(70,38,'Baby Safety Corner Guards','Ongoing','Coordinate supplier contracts','2026-11-07 19:15:43','Low','2024-06-30 21:26:43','2024-06-30 21:26:43'),
	(71,16,'Children''s Backpack','Ongoing','Draft the newsletter content','2026-11-04 21:35:27','Low','2024-11-07 15:15:23','2024-11-07 15:15:23'),
	(72,16,'DIY Lip Balm Kit','Finished','Plan the marketing strategy','2026-05-08 08:51:58','Medium','2024-11-04 17:34:38','2024-11-04 17:34:38'),
	(73,91,'LED Makeup Mirror','Finished','Prepare the quarterly budget','2026-07-20 01:34:00','Medium','2024-05-08 04:21:33','2024-05-08 04:21:33'),
	(74,66,'Mini Air Hockey Table','Ongoing','Follow up with the client','2025-12-03 09:05:15','Low','2024-07-19 21:15:30','2024-07-19 21:15:30'),
	(75,90,'Chocolate Almond Milk','On-hold','Review the codebase','2025-12-25 08:38:05','High','2023-12-04 04:09:16','2023-12-04 04:09:16'),
	(76,54,'Action Camera','On-hold','Submit the financial report','2026-04-09 11:20:40','High','2023-12-26 03:45:42','2023-12-26 03:45:42'),
	(77,96,'Garden Hoses with Expandable Features','Finished','Follow up with the client','2026-02-06 17:54:19','Medium','2024-04-09 06:45:31','2024-04-09 06:45:31'),
	(78,81,'Oven-Baked Potato Chips','On-hold','Plan the marketing strategy','2026-04-18 15:32:07','High','2024-04-18 10:58:28','2024-04-18 10:58:28'),
	(79,33,'Stuffed Peppers with Quinoa','Finished','Design the new website layout','2026-02-22 12:38:47','Medium','2024-02-23 07:56:05','2024-02-23 07:56:05'),
	(80,42,'Insulated Lunch Box','Finished','Conduct performance reviews','2026-03-08 02:22:11','Medium','2024-03-07 21:41:44','2024-03-07 21:41:44'),
	(81,58,'Compressible Packing Cubes','Ongoing','Organize the team building event','2026-06-29 23:54:48','Low','2024-06-29 19:33:01','2024-06-29 19:33:01'),
	(82,39,'Portable Dog Water Bottle','Finished','Attend team meeting','2025-12-27 18:03:09','Medium','2023-12-28 13:11:10','2023-12-28 13:11:10'),
	(83,81,'Organic Brown Rice Cakes','Ongoing','Plan the marketing strategy','2026-05-15 09:40:18','Low','2024-05-15 05:11:02','2024-05-15 05:11:02'),
	(84,51,'Oven-Baked Potato Chips','Finished','Review the codebase','2026-07-22 11:44:23','Medium','2024-07-22 07:26:17','2024-07-22 07:26:17'),
	(85,33,'Caramelized Onion Dip','On-hold','Design the new website layout','2026-10-27 01:09:03','High','2024-10-26 21:06:47','2024-10-26 21:06:47'),
	(86,5,'Coconut Oil','On-hold','Conduct performance reviews','2026-04-11 17:56:39','High','2024-04-11 13:21:52','2024-04-11 13:21:52'),
	(87,6,'Apricot Jam','Finished','Complete the project proposal','2026-01-20 04:03:56','Medium','2024-01-20 23:15:47','2024-01-20 23:15:47'),
	(88,62,'Nutritional Yeast','Finished','Coordinate supplier contracts','2026-01-20 01:59:56','Medium','2024-01-20 21:11:46','2024-01-20 21:11:46'),
	(89,23,'Party Mini Dress','Finished','Arrange the customer feedback session','2026-10-23 03:47:52','Medium','2024-10-22 23:44:58','2024-10-22 23:44:58'),
	(90,83,'Nut Butter Cups','Finished','Review the codebase','2026-07-22 11:12:44','Medium','2024-07-22 06:54:38','2024-07-22 06:54:38'),
	(91,44,'Biodegradable Dog Waste Bags','On-hold','Follow up with the client','2026-10-19 01:37:22','High','2024-10-18 21:33:47','2024-10-18 21:33:47'),
	(92,94,'Foldable Electric Scooter','Ongoing','Conduct performance reviews','2026-07-10 19:47:13','Low','2024-07-10 15:27:12','2024-07-10 15:27:12'),
	(93,78,'Organic Cereal Bars','On-hold','Design the new website layout','2026-06-09 19:54:51','High','2024-06-09 15:29:45','2024-06-09 15:29:45'),
	(94,79,'Magnet Travel Fridge Magnets','Ongoing','Plan the marketing strategy','2026-06-29 15:56:50','Low','2024-06-29 11:35:00','2024-06-29 11:35:00'),
	(95,10,'Buffalo Style Cauliflower Bites','On-hold','Arrange the customer feedback session','2026-11-08 23:24:01','High','2024-11-08 19:23:53','2024-11-08 19:23:53'),
	(96,19,'Garam Masala Spice Blend','Finished','Update the project timeline','2026-09-15 08:13:29','Medium','2024-09-15 04:04:23','2024-09-15 04:04:23'),
	(97,87,'Thai Red Curry Paste','On-hold','Organize the team building event','2026-04-26 19:13:58','High','2024-03-24 00:18:09','2024-03-24 00:18:09'),
	(98,29,'Smart Fitness Scale','On-hold','Review the codebase','2026-06-25 04:32:55','High','2024-04-26 14:41:39','2024-04-26 14:41:39'),
	(99,60,'Cooking Utensil Set','Ongoing','Follow up with the client','2026-04-14 07:45:20','Low','2024-06-25 00:10:21','2024-06-25 00:10:21'),
	(100,67,'Compost Bin','Finished','Update the project timeline','2026-10-13 16:56:06','Medium','2024-04-14 03:10:58','2024-04-14 03:10:58');





-- Populate task_comments table
INSERT INTO task_comments (comment_id, id, task_id, comment, created_at)
VALUES
    (1,58,58,'Reliquisti, nisi te, quoquo modo loqueretur, intellegere, quid diceret? Aliena dixit in physicis nec ea ipsa, quae laudatur, industria, ne fortitudo quidem, sed ista sequimur, ut sine cura metuque vivamus animumque et corpus, quantum efficere possimus, molestia liberemus. Ut.','2024-06-24 20:13:35'),
	(2,87,87,'Aut inertissimae segnitiae est aut fastidii delicatissimi. Mihi quidem nulli satis eruditi videntur, quibus nostra ignota.','2024-05-20 04:51:15'),
	(3,81,81,'Caret, id in voluptate ponit, quod summum bonum consequamur? Clamat Epicurus, is quem vos nimis voluptatibus esse deditum.','2024-04-21 02:23:17'),
	(4,28,28,'Exercitus. -- Quid ex eo est consecutus? -- Laudem et caritatem, quae sunt vitae sine metu vivere. Quae est enim.','2024-10-07 08:51:41'),
	(5,98,98,'Quae est enim aut utilior aut ad beatam aut ad bene vivendum aptior partitio quam illa, qua est usus Epicurus? Qui unum genus posuit earum cupiditatum, quae essent et naturales et necessariae, alterum, quae vis sit, quae quidque efficiat, de materia disseruerunt, vim et causam efficiendi reliquerunt. Sed hoc commune vitium, illae.','2024-09-15 21:55:49'),
	(6,72,72,'Ea ipsa, quae laudatur, industria, ne fortitudo quidem, sed ista sequimur, ut sine cura metuque vivamus animumque et corpus, quantum efficere possimus, molestia liberemus. Ut enim medicorum scientiam non ipsius artis, sed bonae valetudinis causa probamus, et gubernatoris ars, quia bene navigandi.','2024-03-06 11:18:20'),
	(7,82,82,'Illam umbram, quod appellant honestum non tam solido quam splendido nomine, virtutem autem nixam hoc honesto nullam requirere voluptatem atque.','2024-07-21 00:29:26'),
	(8,41,41,'Re cum sit inter doctissimos summa dissensio, quis alienum putet eius esse dignitatis, quam mihi quisque tribuat, quid in omni re doloris amotio successionem efficit voluptatis. Itaque non placuit Epicuro medium esse quiddam.','2024-11-18 00:48:31'),
	(9,91,91,'Illa Torquatis.'' Numquam hoc ita defendit Epicurus neque Metrodorus aut quisquam eorum, qui aut saperet aliquid aut ista didicisset. Et quod quaeritur saepe, cur tam multos legant, quam legendi sunt. Quid enim.','2024-08-13 11:28:43'),
	(10,88,88,'Et gravissimas res consilio ipsius et ratione administrari neque maiorem voluptatem ex hoc percipiatur, quod videamus esse finitum. In dialectica autem.','2024-09-20 11:30:14'),
	(11,84,84,'Est adhibenda, quae et terroribus cupiditatibusque detractis et omnium falsarum opinionum temeritate derepta certissimam se nobis ducem praebeat ad voluptatem. Sapientia enim est una, quae maestitiam pellat ex animis, quae nos vocet.','2024-04-23 17:31:55'),
	(12,13,13,'Delectant. Est autem situm in nobis ut et voluptates repudiandae sint et molestiae non.','2024-10-22 08:27:23'),
	(13,27,27,'Utraque parte audita pronuntiaret eum non talem videri fuisse in imperio, quales eius maiores fuissent, et in conspectum suum venire vetuit, numquid tibi videtur de voluptatibus suis cogitavisse? Sed ut iis bonis erigimur, quae expectamus, sic laetamur iis, quae recordamur. Stulti autem malorum memoria.','2024-02-26 03:18:05'),
	(14,29,29,'Ipsis error est finibus bonorum et malorum, id est voluptatem. Homines optimi non intellegunt totam rationem everti, si ita melius sit, migrare de vita. His rebus peccant, cum e quibus haec efficiantur ignorant. Animi autem voluptates et dolores nasci fatemur e corporis voluptatibus et doloribus -- itaque concedo, quod modo dicebas, cadere causa, si qui e.','2024-09-27 18:49:05'),
	(15,38,38,'Liberatione et vacuitate omnis molestiae gaudemus, omne autem id, quo gaudemus, voluptas est.','2024-01-09 05:48:53'),
	(16,84,84,'Platonis, Aristoteli, Theophrasti orationis ornamenta neglexerit. Nam illud quidem perspicuum est, maximam animi aut voluptatem aut molestiam plus aut ad bene vivendum aptior partitio quam illa, qua est usus Epicurus? Qui unum genus posuit earum cupiditatum, quae essent et naturales et necessariae, alterum, quae.','2024-01-11 15:12:26'),
	(17,83,83,'Liquidae voluptatis et liberae potest. Atqui pugnantibus et contrariis studiis consiliisque semper.','2024-03-02 19:03:47'),
	(18,68,68,'Non asperner, si non habeat, non admodum indoctis, totum hoc.','2024-03-10 15:00:42'),
	(19,2,2,'Adiungimus, quid habent, cur Graeca anteponant iis, quae recordamur. Stulti autem malorum memoria torquentur, sapientes bona praeterita non meminerunt, praesentibus non fruuntur, futura modo.','2024-04-12 11:55:06'),
	(20,34,34,'Quasi delapsa de caelo est ad quiete vivendum, caritatem, praesertim cum omnino nulla sit causa peccandi. Quae enim cupiditates a natura ipsa iudicari. Ea.','2024-07-06 12:58:11'),
	(21,1,1,'Cum praesertim illa perdiscere ludus esset. Quam ob rem voluptas expetenda, fugiendus dolor sit. Sentiri haec putat, ut calere ignem, nivem esse.','2024-09-26 14:46:04'),
	(22,36,36,'De amicitia, quam, si voluptas esset bonum, desideraret.'' -- Ita credo. -- ''Non est igitur voluptas bonum.'' Hoc ne statuam quidem dicturam pater aiebat, si loqui posset. Conclusum est enim in vita tantopere.','2024-09-23 13:09:47'),
	(23,85,85,'Ut Terentianus Chremes non inhumanus, qui novum vicinum non vult ''fodere aut arare aut aliquid ferre denique'' -- non enim illum ab industria, sed ab inliberali labore deterret --, sic isti curiosi, quos offendit noster minime nobis iniucundus labor. Iis.','2024-07-30 18:58:27'),
	(24,68,68,'Inflammat, ut coercendi magis quam dedocendi esse videantur. Invitat igitur vera ratio bene sanos ad iustitiam, aequitatem, fidem, neque homini infanti aut inpotenti iniuste facta conducunt, qui nec facile efficere possit, quod conetur, nec optinere, si effecerit, et opes vel fortunae vel ingenii liberalitati magis conveniunt, qua qui.','2024-03-11 07:29:02'),
	(25,80,80,'Eam non possim accommodare Torquatos nostros? Quos tu paulo ante collegi, abest plurimum et, cum stultorum vitam cum.','2023-11-30 15:25:22'),
	(26,100,100,'Iusteque vivatur, nec sapienter, honeste, iuste, nisi iucunde. Neque enim civitas in seditione beata esse potest nec in discordia dominorum.','2023-12-22 04:32:14'),
	(27,41,41,'Nudus est. Tollit definitiones, nihil de dividendo ac partiendo docet.','2024-03-28 20:46:39'),
	(28,41,41,'Plura, si vita suppetet; et tamen, qui diligenter haec, quae de.','2023-11-29 13:45:31'),
	(29,27,27,'Perpessio dolorum per se ipsa allicit nec patientia nec assiduitas nec vigiliae nec ea ipsa, quae tibi probarentur; si qua.','2024-04-04 05:22:33'),
	(30,62,62,'Maiorem. Ex quo efficiantur ea, quae praeterierunt, acri animo et attento intuemur, tum fit.','2024-09-29 19:13:46'),
	(31,4,4,'Nec tamen id, cuius causa haec finxerat, assecutus est. Nam si omnes atomi declinabunt, nullae umquam cohaerescent, sive.','2024-06-29 10:41:43'),
	(32,92,92,'Nos non interpretum fungimur munere, sed tuemur ea, quae dices, libenter assentiar. Probabo, inquit, modo ista sis aequitate, quam ostendis.','2024-09-03 14:42:04'),
	(33,26,26,'Inmensae et inanes divitiarum, gloriae, dominationis, libidinosarum etiam voluptatum. Accedunt aegritudines, molestiae, maerores, qui exedunt animos conficiuntque curis hominum non intellegentium.','2024-10-02 15:34:23'),
	(34,60,60,'Voluptatem appetere eaque gaudere ut summo bono, dolorem aspernari ut summum malum et, quantum possit, a se ipse dissidens secumque discordans gustare partem ullam liquidae voluptatis et liberae potest. Atqui pugnantibus et contrariis studiis consiliisque semper utens nihil quieti videre, nihil tranquilli potest. Quodsi corporis gravioribus morbis vitae.','2024-07-30 20:41:29'),
	(35,91,91,'Cum Latinis tertio consulatu conflixisse apud Veserim propter voluptatem; quod vero securi percussit filium, privavisse se etiam videtur multis voluptatibus, cum ipsi naturae patrioque amori praetulerit ius maiestatis atque imperii. Quid? T. Torquatus, is qui consul cum Cn. Octavio fuit, cum illam severitatem in eo filio adhibuit, quem in adoptionem D. Silano emancipaverat, ut eum Macedonum.','2024-09-11 22:47:43'),
	(36,68,68,'Instructus semper est in voluptate. Neque enim tempus est ullum, quo non plus voluptatum habeat quam dolorum. Nam.','2024-11-23 20:20:44'),
	(37,61,61,'Omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet, ut et adversa quasi perpetua.','2024-01-30 23:48:17'),
	(38,81,81,'Confirmare, tantum satis esse admonere. Interesse enim inter argumentum conclusionemque rationis et inter mediocrem animadversionem atque admonitionem. Altera occulta quaedam et quasi architecto beatae vitae deduceret? Qui quod tibi ita videri necesse est, non expeteretur, si nihil tale metuamus. Iam illud quidem adduci vix possum, ut ea, quae sint quaeque cernantur, omnia.','2024-04-22 07:21:49'),
	(39,81,81,'Ne temperantiam quidem propter se esse fugiendum. Itaque aiunt hanc.','2024-03-03 19:24:53'),
	(40,98,98,'Vero, quoniam forensibus operis, laboribus, periculis non deseruisse mihi videor praesidium, in quo nihil turpius physico.','2024-07-07 06:59:06'),
	(41,70,70,'Defenditur, tamen eius modi esse iudico, ut nihil homine videatur indignius. Ad maiora enim quaedam nos natura genuit et conformavit.','2023-12-10 09:32:48'),
	(42,38,38,'Eum respirare, numquam adquiescere. Quodsi ne ipsarum quidem virtutum laus, in qua maxime ceterorum philosophorum exultat.','2024-10-27 10:26:49'),
	(43,5,5,'Nec medium nec ultimum nec extremum sit, ita ferri, ut concursionibus inter se dissident atque discordant, ex quo vitam amarissimam necesse est aut fastidii delicatissimi. Mihi quidem nulli satis eruditi videntur, quibus nostra ignota sunt. An ''Utinam ne in.','2024-02-28 06:38:13'),
	(44,22,22,'Accusantibus, quod pecunias praetorem in provincia cepisse.','2024-06-30 21:55:06'),
	(45,21,21,'Alii minuti et angusti aut omnia semper desperantes aut malivoli, invidi, difficiles, lucifugi, maledici, monstruosi, alii autem etiam amaret, cotidieque inter nos ea, quae corrigere vult, mihi quidem depravare videatur. Ille atomos quas appellat, id est voluptatem et dolorem? Sunt autem quidam Epicurei timidiores paulo contra vestra convicia, sed tamen satis acuti.','2024-10-21 05:17:29'),
	(46,29,29,'Et constantia contra metum religionis et sedatio animi omnium rerum occultarum ignoratione sublata et.','2024-07-30 16:03:52'),
	(47,13,13,'Fugiat aliquid, praeter voluptatem et dolorem. Ad haec et quae sequamur et quae sequamur et quae sequamur et.','2024-07-03 17:14:05'),
	(48,81,81,'Tota res (est) ficta pueriliter, tum ne efficit quidem, quod vult. Nam et ipsa declinatio ad libidinem fingitur -- ait enim declinare atomum sine causa; quo nihil posset fieri minus; ita effici complexiones et copulationes et adhaesiones atomorum inter se, ex quo efficeretur mundus omnesque partes mundi, quaeque.','2024-09-17 02:50:14'),
	(49,82,82,'Esse censet, quantus videtur, vel paulo aut maiorem aut minorem. Ita, quae mutat, ea corrumpit, quae sequitur sunt tota Democriti, atomi.','2024-09-14 23:26:02'),
	(50,3,3,'Ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum defuturum, quas natura non depravata desiderat. Et quem ad modum eae semper voluptatibus inhaererent, eadem de amicitia disputatum. Alii cum eas voluptates, quae ad amicos pertinerent, negarent.','2024-11-15 22:58:00'),
	(51,64,64,'Coniunctione tali sit aptius. Quibus ex omnibus iudicari potest non modo non impediri rationem amicitiae, si summum bonum consequamur? Clamat Epicurus, is quem vos nimis voluptatibus esse deditum dicitis; non posse iucunde vivi, nisi sapienter, honeste iusteque vivatur, nec sapienter, honeste, iuste, nisi iucunde. Neque enim civitas in seditione beata esse potest.','2024-07-21 08:32:03'),
	(52,95,95,'Et apertam et simplicem et directam viam! Cum enim certe nihil homini possit melius esse quam Graecam. Quando enim nobis, vel dicam aut oratoribus bonis aut poetis, postea quidem quam fuit quem imitarentur, ullus orationis vel copiosae vel elegantis ornatus.','2024-08-07 01:13:11'),
	(53,64,64,'Sequatur, si illa mala sint, laetitia, si bona. O praeclaram beate vivendi et apertam et simplicem et directam viam! Cum enim certe nihil homini possit melius esse quam Graecam. Quando enim nobis, vel dicam aut oratoribus bonis aut poetis, postea.','2024-04-12 00:38:07'),
	(54,16,16,'Distinguantur ostendit; iudicia rerum in sensibus ponit, quibus si semel aliquid falsi pro vero probatum sit, sublatum esse.','2024-10-02 05:56:14'),
	(55,78,78,'Neque feci adhuc nec mihi tamen, ne faciam, interdictum puto. Locos quidem quosdam, si videbitur, transferam, et maxime ab iis, quos ego posse iudicare arbitrarer, plura suscepi veritus ne movere hominum studia viderer.','2024-02-29 19:33:27'),
	(56,35,35,'Primo inter nos ea, quae sensum moveat, nulla successerit, eoque intellegi potest quanta voluptas sit non.','2023-12-13 23:23:48'),
	(57,79,79,'Recta et honesta quae sint, ea facere ipsa per se ipsam causam non fuisse. -- Torquem detraxit hosti. -- Et.','2024-02-13 15:53:19'),
	(58,20,20,'Pertinerent ad bene beateque vivendo a Platone disputata sunt, haec explicari non placebit Latine? Quid? Si nos non interpretum fungimur munere, sed tuemur ea, quae corrigere.','2024-02-09 12:52:19'),
	(59,6,6,'Effici complexiones et copulationes et.','2024-03-09 16:54:33'),
	(60,24,24,'Semper occultum. Plerumque improborum facta primo suspicio insequitur, dein sermo.','2024-01-12 01:31:06'),
	(61,79,79,'Se repellere, idque facere nondum depravatum ipsa natura divitias, quibus contenta sit, et.','2024-09-16 22:45:40'),
	(62,23,23,'De dividendo ac partiendo docet, non quo modo efficiatur concludaturque ratio tradit, non qua via captiosa.','2024-06-03 23:07:38'),
	(63,49,49,'In nostris poetis aut inertissimae segnitiae est aut in voluptate esse aut in.','2024-09-20 06:53:49'),
	(64,40,40,'Quaerendi defatigatio turpis est, cum id, quod ipsi statuerunt, non possunt, victi et debilitati obiecta specie voluptatis tradunt se libidinibus constringendos nec quid eventurum sit provident ob eamque debilitatem animi multi parentes, multi amicos, non nulli patriam, plerique autem se ipsos penitus perdiderunt, sic robustus animus et a falsis initiis profecta vera esse non possunt et.','2023-12-05 16:48:44'),
	(65,9,9,'Quos omnis dicendum breviter existimo. Quamquam philosophiae quidem vituperatoribus satis responsum est eo libro, quo.','2024-07-15 23:56:15'),
	(66,35,35,'Tamen, quae te ipsum probaturum esse confidam. Certe, inquam, pertinax non ero tibique, si mihi probabis ea, quae dices, libenter assentiar. Probabo, inquit, modo ista sis aequitate, quam ostendis. Sed uti oratione perpetua malo quam interrogare aut interrogari. Ut placet, inquam. Tum dicere exorsus est. Primum igitur, inquit.','2024-11-06 19:03:44'),
	(67,22,22,'Aiebat, si loqui posset. Conclusum est enim in vita tantopere.','2024-07-15 05:46:47'),
	(68,13,13,'Ad te ne Graecis quidem cedentem in philosophia audeam scribere? Quamquam a te ipso id quidem facio provocatus gratissimo mihi libro.','2024-01-21 23:34:30'),
	(69,24,24,'Est, quae non modo fautrices fidelissimae, sed etiam praetereat omnes voluptates, dolores denique quosvis suscipere malit quam deserere ullam officii partem, ad ea, quae dices, libenter assentiar. Probabo, inquit, modo ista sis aequitate, quam ostendis. Sed uti oratione perpetua malo quam interrogare aut interrogari.','2024-09-06 00:30:57'),
	(70,96,96,'Ipsos quidem Graecos est cur dubitemus dicere et sapientiam propter voluptates expetendam et insipientiam propter molestias esse fugiendam? Eademque ratione ne.','2024-07-10 16:07:56'),
	(71,72,72,'Autem illud vel maxime, quod ipsa natura, ut ait.','2024-03-31 22:13:38'),
	(72,73,73,'Modo non repugnantibus, verum etiam approbantibus nobis. Sic enim ab Epicuro sapiens semper beatus inducitur: finitas habet cupiditates, neglegit mortem.','2024-09-09 22:13:46'),
	(73,31,31,'Voluptatis tradunt se libidinibus constringendos nec quid eventurum sit provident ob eamque debilitatem animi multi parentes, multi amicos, non nulli patriam, plerique autem se ipsos penitus perdiderunt, sic robustus animus.','2024-08-01 01:53:27'),
	(74,41,41,'Miserum est, ob eamque debilitatem animi multi parentes, multi amicos, non nulli patriam, plerique autem se ipsos amentur. Etenim si delectamur, cum scribimus, quis.','2024-02-05 19:05:58'),
	(75,27,27,'Laetetur, quid est, quod huc possit, quod melius sit, accedere? Statue contra aliquem confectum tantis animi corporisque doloribus, quanti in hominem maximi cadere possunt, nulla spe proposita fore.','2023-12-17 16:05:13'),
	(76,14,14,'Et quidem se texit, ne interiret. -- At magnum periculum adiit. -- In oculis quidem exercitus. -- Quid ex eo perciperet corpore voluptatem, aut cum Latinis tertio consulatu conflixisse apud Veserim propter voluptatem.','2024-02-21 19:31:25'),
	(77,87,87,'Statuat industriae? Nam ut Terentianus Chremes non inhumanus.','2024-09-09 10:55:21'),
	(78,55,55,'A te ipso id quidem licebit iis existimare.','2024-02-15 10:29:28'),
	(79,5,5,'Accessio potest fieri, quanta ad.','2024-05-22 02:43:23'),
	(80,80,80,'Omnia iudicia rerum in sensibus ponit, quibus si semel aliquid falsi pro vero probatum sit, sublatum esse omne iudicium veri et falsi putat. Confirmat autem illud vel maxime, quod ipsa natura, ut ait ille, sciscat et probet, id est laborum et dolorum fuga. Et.','2024-04-20 03:45:36'),
	(81,48,48,'Hominem maximi cadere possunt, nulla spe proposita fore levius aliquando, nulla.','2023-12-27 02:07:18'),
	(82,18,18,'Nullo a principio, sed ex aeterno tempore intellegi convenire. Epicurus.','2024-04-01 08:21:17'),
	(83,20,20,'Gloriae, dominationis, libidinosarum etiam voluptatum. Accedunt aegritudines, molestiae, maerores, qui exedunt animos conficiuntque curis.','2024-02-14 02:40:38'),
	(84,51,51,'Quisquam stultus non horum morborum.','2024-01-11 15:48:50'),
	(85,68,68,'Alii autem, quibus ego assentior, dum modo de isdem rebus ne Graecos quidem legendos putent. Res vero bonas verbis electis.','2024-11-08 13:09:20'),
	(86,59,59,'Hunc mundi ornatum efficere non.','2024-08-14 09:49:42'),
	(87,32,32,'In ea voluptate velit esse, quam nihil praetermittatur quod.','2024-08-17 22:24:52'),
	(88,56,56,'Tantalo semper impendet, tum superstitio, qua.','2024-03-15 19:06:40'),
	(89,70,70,'Logikh dicitur, iste vester plane, ut mihi videtur, expediunt. Ut enim sapientiam, temperantiam, fortitudinem copulatas esse docui cum voluptate, ut ab Homero Ennius, Afranius a Menandro solet. Nec vero, ut noster Lucilius, recusabo, quo minus id, quod quaeritur, sit pulcherrimum. Etenim si loca, si.','2024-04-22 00:17:38'),
	(90,22,22,'Se Latina scripta dicunt contemnere. In quibus tam multis tamque variis ab ultima antiquitate repetitis tria vix amicorum paria reperiuntur, ut ad Orestem pervenias.','2024-03-03 09:56:10'),
	(91,21,21,'Aptior partitio quam illa, qua est usus Epicurus? Qui unum genus posuit earum cupiditatum, quae essent et naturales et necessariae, alterum, quae vis sit, quae quidque efficiat, de materia disseruerunt, vim et causam efficiendi reliquerunt. Sed.','2023-12-11 01:54:31'),
	(92,45,45,'Depravatum ipsa natura incorrupte atque integre iudicante. Itaque negat opus esse ratione neque disputatione, quam ob rem voluptas.','2024-01-14 07:55:41'),
	(93,15,15,'Gravissimo bello animadversionis metu contineret, saluti prospexit civium, qua intellegebat contineri suam. Atque haec.','2024-04-27 17:59:01'),
	(94,20,20,'Ad modum, quaeso, interpretaris? Sicine eos censes aut in poetis evolvendis, ut.','2024-10-08 23:15:18'),
	(95,16,16,'Epicuro sapiens semper beatus inducitur.','2024-06-12 03:50:22'),
	(96,74,74,'Ferantur, deinde eadem illa atomorum, in quo nihil nec.','2024-02-03 15:02:12'),
	(97,72,72,'Dicturam pater aiebat, si loqui posset. Conclusum est enim contra.','2023-12-12 16:27:09'),
	(98,10,10,'Autem a facillimis ordiamur, prima veniat in medium Epicuri ratio, quae plerisque notissima est. Quam a nobis philosophia defensa et collaudata est, cum id, quod maxime placeat, facere possimus, omnis voluptas assumenda est, omnis dolor repellendus.','2024-09-24 20:13:46'),
	(99,90,90,'Tamen, qui diligenter haec, quae vitam omnem continent, neglegentur? Nam, ut sint opera, studio, labore meo doctiores cives.','2024-09-11 01:19:17'),
	(100,78,78,'Umquam controversia, quid ego intellegerem, sed quid probarem. Quid.','2024-11-19 07:46:12');



-- Populate project_member table
INSERT INTO project_member (id, project_id, "role")
VALUES
(99, 100, 'Project manager'),
(41, 34, 'Project manager'),
(6, 83, 'Project manager'),
(47, 54, 'Project manager'),
(25, 77, 'Project owner'),
(17, 96, 'Project member'),
(20, 68, 'Project member'),
(89, 34, 'Project member'),
(97, 2, 'Project member'),
(59, 53, 'Project member'),
(89, 17, 'Project manager'),
(5, 6, 'Project owner'),
(54, 92, 'Project manager'),
(15, 73, 'Project member'),
(1, 85, 'Project member'),
(76, 19, 'Project member'),
(36, 96, 'Project manager'),
(74, 58, 'Project manager'),
(7, 13, 'Project member'),
(67, 60, 'Project member'),
(66, 37, 'Project owner'),
(59, 41, 'Project member'),
(92, 9, 'Project manager'),
(83, 40, 'Project member'),
(17, 65, 'Project manager'),
(60, 46, 'Project manager'),
(29, 70, 'Project member'),
(33, 2, 'Project manager'),
(16, 92, 'Project owner'),
(95, 75, 'Project member'),
(30, 45, 'Project member'),
(59, 84, 'Project manager'),
(20, 28, 'Project member'),
(38, 79, 'Project manager'),
(87, 53, 'Project manager'),
(41, 2, 'Project member'),
(8, 36, 'Project member'),
(60, 71, 'Project member'),
(27, 79, 'Project member'),
(65, 90, 'Project member'),
(16, 70, 'Project manager'),
(40, 35, 'Project member'),
(74, 47, 'Project member'),
(68, 57, 'Project manager'),
(22, 91, 'Project owner'),
(4, 77, 'Project member'),
(69, 88, 'Project manager'),
(1, 3, 'Project manager'),
(26, 47, 'Project manager'),
(58, 91, 'Project member'),
(77, 100, 'Project manager'),
(21, 95, 'Project manager'),
(47, 49, 'Project manager'),
(66, 89, 'Project owner'),
(2, 28, 'Project manager'),
(14, 73, 'Project owner'),
(75, 19, 'Project manager'),
(81, 44, 'Project member'),
(10, 72, 'Project owner'),
(38, 11, 'Project member'),
(18, 59, 'Project owner'),
(64, 80, 'Project manager'),
(36, 10, 'Project member'),
(92, 67, 'Project manager'),
(63, 91, 'Project owner'),
(17, 33, 'Project manager'),
(41, 50, 'Project owner'),
(45, 29, 'Project manager'),
(93, 88, 'Project member'),
(11, 57, 'Project member'),
(40, 73, 'Project owner'),
(2, 70, 'Project member'),
(63, 13, 'Project manager'),
(70, 9, 'Project owner'),
(28, 57, 'Project manager'),
(37, 59, 'Project owner'),
(61, 50, 'Project manager'),
(15, 68, 'Project member'),
(55, 40, 'Project member'),
(7, 99, 'Project manager'),
(32, 19, 'Project manager'),
(42, 89, 'Project member'),
(50, 72, 'Project manager'),
(85, 55, 'Project owner'),
(22, 5, 'Project member'),
(4, 64, 'Project manager'),
(39, 43, 'Project member'),
(54, 25, 'Project member'),
(3, 16, 'Project manager'),
(93, 24, 'Project member'),
(85, 39, 'Project manager'),
(45, 64, 'Project manager'),
(61, 70, 'Project member'),
(25, 59, 'Project manager'),
(66, 25, 'Project manager'),
(33, 83, 'Project member'),
(49, 42, 'Project manager'),
(76, 44, 'Project member'),
(53, 28, 'Project manager'),
(91, 72, 'Project manager'),
(5, 93, 'Project manager'),
(45, 52, 'Project manager'),
(48, 69, 'Project member'),
(84, 70, 'Project member'),
(81, 45, 'Project manager'),
(59, 62, 'Project member'),
(71, 39, 'Project member'),
(92, 20, 'Project member'),
(74, 51, 'Project owner'),
(59, 68, 'Project manager'),
(62, 80, 'Project member'),
(44, 92, 'Project member'),
(28, 58, 'Project manager'),
(31, 43, 'Project manager'),
(61, 32, 'Project manager'),
(76, 69, 'Project owner'),
(80, 54, 'Project member'),
(69, 91, 'Project member');








-- Populate favorited table
INSERT INTO favorited (id, project_id, checks)
VALUES
    (10, 22, True),
(29, 1, True),
(29, 36, True),
(47, 96, True),
(27, 38, True),
(86, 10, False),
(4, 71, False),
(30, 79, True),
(89, 96, False),
(76, 87, True),
(13, 56, False),
(43, 35, True),
(39, 88, False),
(48, 30, False),
(10, 96, False),
(36, 18, False),
(54, 66, False),
(76, 54, True),
(57, 22, True),
(72, 78, True),
(96, 91, False),
(67, 98, False),
(49, 15, False),
(31, 63, False),
(97, 71, True),
(27, 30, False),
(57, 92, True),
(11, 91, False),
(6, 42, True),
(77, 21, True),
(42, 6, True),
(81, 40, True),
(72, 67, False),
(56, 33, False),
(73, 90, False),
(43, 66, False),
(3, 10, True),
(37, 81, False),
(89, 14, True),
(4, 96, False),
(95, 66, True),
(63, 54, False),
(8, 77, False),
(82, 16, True),
(80, 41, False),
(81, 17, True),
(79, 39, True),
(77, 81, False),
(88, 100, False),
(45, 45, True),
(90, 69, False),
(51, 93, True),
(51, 54, False),
(59, 59, False),
(95, 29, True),
(41, 2, False),
(76, 2, True),
(34, 57, True),
(99, 3, True),
(99, 5, False),
(80, 51, True),
(7, 50, True),
(27, 83, False),
(27, 70, True),
(100, 5, True),
(32, 47, False),
(84, 84, True),
(73, 26, False),
(78, 67, False),
(2, 46, True),
(41, 86, False),
(24, 53, False),
(29, 18, True),
(77, 16, True),
(33, 42, False),
(99, 54, False),
(78, 93, True),
(46, 34, True),
(83, 68, True),
(87, 4, False),
(1, 83, True),
(14, 64, False),
(93, 64, False),
(92, 43, True),
(71, 32, True),
(12, 56, False),
(49, 36, False),
(21, 84, True),
(70, 51, True),
(85, 40, True),
(91, 4, True),
(71, 95, True),
(100, 68, True),
(66, 52, True),
(44, 26, True),
(42, 64, False),
(65, 60, False),
(41, 91, False),
(43, 10, False),
(56, 5, False);


-- Populate task_notif table
INSERT INTO task_notif (notif_id, task_id)
VALUES
    (1,8),
	(2,78),
	(3,9),
	(4,74),
	(5,38),
	(6,68),
	(7,44),
	(8,48),
	(9,11),
	(10,49),
	(11,10),
	(12,69),
	(13,59),
	(14,97),
	(15,37),
	(16,84),
	(17,42),
	(18,79),
	(19,80),
	(20,4),
	(21,90),
	(22,19),
	(23,72),
	(24,23),
	(25,99),
	(26,59),
	(27,17),
	(28,14),
	(29,63),
	(30,83),
	(31,16),
	(32,87),
	(33,12),
	(34,18),
	(35,20),
	(36,87),
	(37,52),
	(38,90),
	(39,97),
	(40,81),
	(41,14),
	(42,89),
	(43,33),
	(44,49),
	(45,78),
	(46,18),
	(47,48),
	(48,15),
	(49,98),
	(50,13);




-- Populate invite_notif table
INSERT INTO invite_notif (invite_notif_id, accepted, notif_id, project_id)
VALUES
	(1,TRUE,51,49),
	(2,FALSE,52,78),
	(3,FALSE,53,19),
	(4,FALSE,54,76),
	(5,TRUE,55,57),
	(6,FALSE,56,66),
	(7,FALSE,57,54),
	(8,TRUE,58,74),
	(9,FALSE,59,38),
	(10,FALSE,60,37),
	(11,TRUE,61,59),
	(12,TRUE,62,34),
	(13,FALSE,63,84),
	(14,FALSE,64,26),
	(15,TRUE,65,94),
	(16,TRUE,66,47),
	(17,TRUE,67,8),
	(18,FALSE,68,89),
	(19,TRUE,69,27),
	(20,TRUE,70,80),
	(21,FALSE,71,23),
	(22,TRUE,72,65),
	(23,FALSE,73,91),
	(24,TRUE,74,94),
	(25,FALSE,75,54),
	(26,FALSE,76,25),
	(27,FALSE,77,22),
	(28,TRUE,78,85),
	(29,TRUE,79,22),
	(30,TRUE,80,76),
	(31,TRUE,81,86),
	(32,TRUE,82,28),
	(33,FALSE,83,7),
	(34,FALSE,84,62),
	(35,TRUE,85,41),
	(36,FALSE,86,10),
	(37,TRUE,87,75),
	(38,TRUE,88,84),
	(39,TRUE,89,16),
	(40,TRUE,90,51),
	(41,TRUE,91,58),
	(42,TRUE,92,87),
	(43,TRUE,93,75),
	(44,FALSE,94,32),
	(45,FALSE,95,16),
	(46,TRUE,96,50),
	(47,TRUE,97,80),
	(48,TRUE,98,1),
	(49,FALSE,99,9),
	(50,TRUE,100,44);



-- Populate authenticated_user_notif table
INSERT INTO authenticated_user_notif (id, notif_id)
VALUES
(1,1),
	(2,2),
	(3,3),
	(4,4),
	(5,5),
	(6,6),
	(7,7),
	(8,8),
	(9,9),
	(10,10),
	(11,11),
	(12,12),
	(13,13),
	(14,14),
	(15,15),
	(16,16),
	(17,17),
	(18,18),
	(19,19),
	(20,20),
	(21,21),
	(22,22),
	(23,23),
	(24,24),
	(25,25),
	(26,26),
	(27,27),
	(28,28),
	(29,29),
	(30,30),
	(31,31),
	(32,32),
	(33,33),
	(34,34),
	(35,35),
	(36,36),
	(37,37),
	(38,38),
	(39,39),
	(40,40),
	(41,41),
	(42,42),
	(43,43),
	(44,44),
	(45,45),
	(46,46),
	(47,47),
	(48,48),
	(49,49),
	(50,50),
	(51,51),
	(52,52),
	(53,53),
	(54,54),
	(55,55),
	(56,56),
	(57,57),
	(58,58),
	(59,59),
	(60,60),
	(61,61),
	(62,62),
	(63,63),
	(64,64),
	(65,65),
	(66,66),
	(67,67),
	(68,68),
	(69,69),
	(70,70),
	(71,71),
	(72,72),
	(73,73),
	(74,74),
	(75,75),
	(76,76),
	(77,77),
	(78,78),
	(79,79),
	(80,80),
	(81,81),
	(82,82),
	(83,83),
	(84,84),
	(85,85),
	(86,86),
	(87,87),
	(88,88),
	(89,89),
	(90,90),
	(91,91),
	(92,92),
	(93,93),
	(94,94),
	(95,95),
	(96,96),
	(97,97),
	(98,98),
	(99,99),
	(100,100);

    
SELECT MAX(id) FROM authenticated_user;

SELECT setval('authenticated_user_id_seq', (SELECT MAX(id) FROM authenticated_user));

SELECT MAX(project_id) FROM project;

SELECT setval('project_project_id_seq', (SELECT MAX(project_id) FROM project));

SELECT MAX(task_id) FROM task;

SELECT setval('task_task_id_seq', (SELECT MAX(task_id) FROM task));

SELECT MAX(comment_id) FROM task_comments;

SELECT setval('task_comments_comment_id_seq', (SELECT MAX(comment_id) FROM task_comments));

SELECT MAX(invite_notif_id) FROM invite_notif;

SELECT setval('invite_notif_invite_notif_id_seq', (SELECT MAX(invite_notif_id) FROM invite_notif));


SELECT MAX(notif_id) FROM notif;

SELECT setval('notif_notif_id_seq', (SELECT MAX(notif_id) FROM invite_notif));



-- Populate user_task table
INSERT INTO user_task (id, task_id)
VALUES
    (1, 23),
(1, 62),
(2, 13),
(2, 14),
(2, 16),
(2, 30),
(3, 17),
(3, 42),
(3, 53),
(3, 72),
(3, 74),
(4, 20),
(4, 45),
(4, 71),
(4, 87),
(5, 11),
(5, 19),
(5, 29),
(5, 38),
(5, 59),
(6, 15),
(6, 34),
(6, 60),
(6, 64),
(6, 79),
(7, 18),
(7, 23),
(7, 65),
(7, 82),
(7, 97),
(8, 31),
(8, 44),
(8, 69),
(8, 92),
(9, 24),
(9, 46),
(9, 67),
(9, 76),
(10, 21),
(10, 33),
(10, 50),
(10, 88),
(11, 27),
(11, 58),
(11, 84),
(12, 35),
(12, 41),
(12, 78),
(12, 86),
(13, 22),
(13, 40),
(13, 57),
(13, 94),
(14, 25),
(14, 36),
(14, 66),
(14, 80),
(15, 26),
(15, 43),
(15, 68),
(15, 83),
(16, 39),
(16, 49),
(16, 73),
(16, 98),
(17, 28),
(17, 37),
(17, 55),
(17, 89),
(18, 32),
(18, 56),
(18, 61),
(18, 99),
(19, 48),
(19, 70),
(19, 90),
(19, 95),
(20, 54),
(20, 75),
(20, 85),
(20, 96),
(21, 52),
(21, 81),
(22, 47),
(22, 63),
(22, 91),
(23, 51),
(23, 93),
(24, 42),
(24, 77),
(25, 30),
(25, 62),
(26, 53),
(26, 72),
(27, 46),
(27, 99),
(28, 41);

	
SET enable_seqscan TO off;
DROP INDEX IF EXISTS idx_post_creation_date;
CREATE INDEX idx_post_creation_date ON post USING btree (post_creation);
DROP INDEX IF EXISTS idx_project_archived_status;
CREATE INDEX idx_project_archived_status ON project USING btree (archived_status);
DROP INDEX IF EXISTS idx_notif_created_at;
CREATE INDEX idx_notif_created_at ON notif USING btree (created_at);

