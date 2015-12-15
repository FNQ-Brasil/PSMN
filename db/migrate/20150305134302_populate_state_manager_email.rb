class PopulateStateManagerEmail < ActiveRecord::Migration
  def up
    execute "insert into StateManagerEmail (StateId,Email) values (1,'maria@ac.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (2,'pollyanna.melo@al.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (3,'osiana.nogueira@am.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (4,'gilvana@ap.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (5,'suely.paula@ba.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (6,'christine.satiro@ce.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (7,'mayara.pessoa@df.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (8,'marceliy.bridi@es.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (9,'lucia@sebraego.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (10,'amparo@ma.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (11,'mario.rezende@sebraemg.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (12,'mariadelourdes.ortiz@ms.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (13,'carla.vecchi@mt.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (14,'renata@pa.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (15,'mjmp@pb.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (16,'robertomoreira@pe.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (17,'luzinete@pi.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (18,'lhahn@pr.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (19,'eoliveira@rj.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (20,'etelvina@rn.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (21,'cristiano.rodrigues@ro.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (22,'eliene.araujo@rr.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (23,'roseli@sebrae-rs.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (24,'ana@sc.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (25,'aldeci.andrade@se.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (26,'anamariab@sp.sebrae.com.br')"
    execute "insert into StateManagerEmail (StateId,Email) values (27,'barbara.nunes@to.sebrae.com.br')"
  end

  def down
    execute 'delete from StateManagerEmail'
  end
end
