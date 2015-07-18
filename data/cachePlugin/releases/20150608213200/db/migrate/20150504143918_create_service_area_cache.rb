class CreateServiceAreaCache < ActiveRecord::Migration
  def change
    create_table :ServiceAreaCache, primary_key: 'Id' do |t|
      t.integer :RegionalId
      t.integer :StateId
      t.integer :CityId
      t.integer :NeighborhoodId
    end

    add_index :ServiceAreaCache, :RegionalId
    add_index :ServiceAreaCache, :StateId
    add_index :ServiceAreaCache, :CityId
    add_index :ServiceAreaCache, :NeighborhoodId
  end
end
