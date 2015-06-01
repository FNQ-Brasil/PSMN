class AddSummaryToQuestion < ActiveRecord::Migration
  def change
    add_column 'Question', 'Summary', :text
  end
end
