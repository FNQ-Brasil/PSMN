class AddNirfToEnterprise < ActiveRecord::Migration
  def change
    add_column 'Enterprise', 'Nirf', :string, limit: 50
    add_column 'Enterprise', 'FarmSize', :decimal, precision: 14, scale: 2
  end
end
