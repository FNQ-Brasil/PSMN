class CreateManagementThemeQuestion < ActiveRecord::Migration
  def change
    create_table 'ManagementThemeQuestion', primary_key: 'Id' do |t|
      t.integer 'ManagementThemeId'
      t.integer 'QuestionId'
      t.decimal 'QuestionWeight', precision: 5, scale: 2
    end

    add_index 'ManagementThemeQuestion', 'ManagementThemeId'
    add_index 'ManagementThemeQuestion', 'QuestionId'
  end
end
