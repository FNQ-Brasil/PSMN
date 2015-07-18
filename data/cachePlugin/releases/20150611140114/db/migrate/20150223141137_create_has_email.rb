class CreateHasEmail < ActiveRecord::Migration
  def up
    add_hasnt_email_column
    update_hasnt_email_column
  end

  def add_hasnt_email_column
    add_column 'Enterprise', 'HasntEmail', :boolean

    add_index 'Enterprise', 'HasntEmail'
  end

  def update_hasnt_email_column
    execute "UPDATE Enterprise SET HasntEmail = 1 WHERE EmailDefault = ''"
    execute "UPDATE Enterprise SET HasntEmail = 0 WHERE EmailDefault <> ''"
  end

  def down
    remove_column 'Enterprise', 'HasntEmail'
  end
end
