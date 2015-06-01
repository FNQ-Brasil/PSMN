# encoding: utf-8

class CreateCourseType < ActiveRecord::Migration
  def up
    create_course_type_table
    populate_table
  end

  def create_course_type_table
    create_table 'CourseType', primary_key: 'Id' do |t|
      t.string 'Name'
    end

    add_index 'CourseType', 'Name'
  end

  def populate_table
    execute  "INSERT INTO CourseType(Id,Name) VALUES (1,'À distância')"
    execute  "INSERT INTO CourseType(Id,Name) VALUES (2,'Presencial')"
  end

  def down
    drop_table 'CourseType'
  end
end
