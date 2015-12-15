class AddCreatedAtToEcac < ActiveRecord::Migration
  def change
    add_column 'EnterpriseCategoryAwardCompetition', 'CreatedAt', :datetime
  end
end
