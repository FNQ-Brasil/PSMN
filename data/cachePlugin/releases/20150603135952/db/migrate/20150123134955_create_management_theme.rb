class CreateManagementTheme < ActiveRecord::Migration
  def change
    create_table 'ManagementTheme', primary_key: 'Id' do |t|
      t.string 'Name'
    end

    add_index 'ManagementTheme', 'Name'
  end
end
