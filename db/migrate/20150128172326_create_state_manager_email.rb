class CreateStateManagerEmail < ActiveRecord::Migration
  def change
    create_table 'StateManagerEmail', primary_key: 'Id' do |t|
      t.integer 'StateId'
      t.string 'Email'
    end

    add_index 'StateManagerEmail', 'StateId'
  end
end
