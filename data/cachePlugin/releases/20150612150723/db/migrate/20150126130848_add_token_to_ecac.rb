class AddTokenToEcac < ActiveRecord::Migration
  def change
    add_column 'EnterpriseCategoryAwardCompetition', 'Token', :string
    add_column 'EnterpriseCategoryAwardCompetition', 'Verified', :boolean, default: false

    add_index 'EnterpriseCategoryAwardCompetition', 'Token'
    add_index 'EnterpriseCategoryAwardCompetition', ['EnterpriseId', 'Verified'], name: 'IDX_ECAC_EnterpriseId_Verified'
  end
end
