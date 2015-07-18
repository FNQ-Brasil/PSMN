class CreateVerifyPublicPermission < ActiveRecord::Migration
  def up
    execute  "INSERT INTO Role_Resource_Privilege(RoleId,ResourceId,Privilege) VALUES (2,27,'verify')"
  end

  def down
    execute "DELETE FROM Role_Resource_Privilege WHERE RoleId = 2 and ResourceId = 27 and Privilege = 'verify'"
  end
end
