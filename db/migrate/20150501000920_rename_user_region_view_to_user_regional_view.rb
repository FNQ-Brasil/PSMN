class RenameUserRegionViewToUserRegionalView < ActiveRecord::Migration
  def up
    execute 'rename table vw_UserRegion to vw_UserRegional'
  end

  def down
    execute 'rename table vw_UserRegional to vw_UserRegion'
  end
end
