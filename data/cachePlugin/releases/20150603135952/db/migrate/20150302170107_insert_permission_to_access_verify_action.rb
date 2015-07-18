class InsertPermissionToAccessVerifyAction < ActiveRecord::Migration
  def up
    execute  "INSERT INTO Role_Resource_Privilege(RoleId,ResourceId,Privilege) VALUES (1,47,'verify')"
    execute  "INSERT INTO Role_Resource_Privilege(RoleId,ResourceId,Privilege) VALUES (34,47,'verify')"
  end

  def down
    execute "DELETE FROM Role_Resource_Privilege WHERE RoleId = 1 and ResourceId = 47 and Privilege = 'verify'"
    execute "DELETE FROM Role_Resource_Privilege WHERE RoleId = 34 and ResourceId = 47 and Privilege = 'verify'"
  end
end
