class DeletePresencialCourses < ActiveRecord::Migration
  def up
    execute "DELETE FROM Course WHERE CourseTypeId = 2 OR Code = '145'"
  end
end
