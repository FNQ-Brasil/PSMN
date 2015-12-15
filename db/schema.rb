# encoding: UTF-8
# This file is auto-generated from the current state of the database. Instead
# of editing this file, please use the migrations feature of Active Record to
# incrementally modify your database, and then regenerate this schema definition.
#
# Note that this schema.rb definition is the authoritative source for your
# database schema. If you need to create the application database on another
# system, you should be using db:schema:load, not running all the migrations
# from scratch. The latter is a flawed and unsustainable approach (the more migrations
# you'll amass, the slower it'll run and the greater likelihood for issues).
#
# It's strongly recommended that you check this file into your version control system.

ActiveRecord::Schema.define(version: 20150504143918) do

  create_table "Address", primary_key: "Id", force: :cascade do |t|
    t.string  "Uf",               limit: 2,   null: false
    t.integer "CityId",           limit: 4,   null: false
    t.string  "NameAloneLog",     limit: 300, null: false
    t.string  "NameFullLog",      limit: 300, null: false
    t.integer "NeighborhoodId",   limit: 4,   null: false
    t.string  "StreetType",       limit: 300, null: false
    t.string  "Cep",              limit: 300, null: false
    t.integer "UfCode",           limit: 4,   null: false
    t.string  "StreetCompletion", limit: 300, null: false
  end

  add_index "Address", ["Cep"], name: "Cep", length: {"Cep"=>255}, using: :btree
  add_index "Address", ["CityId"], name: "CityId", using: :btree
  add_index "Address", ["NameAloneLog"], name: "NameAloneLog", length: {"NameAloneLog"=>255}, using: :btree
  add_index "Address", ["NeighborhoodId"], name: "NeighborhoodId", using: :btree
  add_index "Address", ["StreetCompletion"], name: "StreetCompletion", length: {"StreetCompletion"=>255}, using: :btree
  add_index "Address", ["StreetType"], name: "StreetType", length: {"StreetType"=>255}, using: :btree
  add_index "Address", ["Uf"], name: "Uf", using: :btree
  add_index "Address", ["UfCode"], name: "UfCode", using: :btree

  create_table "AddressEnterprise", primary_key: "Id", force: :cascade do |t|
    t.integer "AddressId",        limit: 4
    t.integer "EnterpriseId",     limit: 4,   null: false
    t.integer "CityId",           limit: 4
    t.integer "StateId",          limit: 4
    t.string  "Cep",              limit: 10
    t.string  "StreetNameFull",   limit: 200
    t.string  "StreetNumber",     limit: 10
    t.string  "StreetCompletion", limit: 200
    t.integer "NeighborhoodId",   limit: 4
  end

  add_index "AddressEnterprise", ["AddressId"], name: "AddressId", using: :btree
  add_index "AddressEnterprise", ["CityId"], name: "CityId", using: :btree
  add_index "AddressEnterprise", ["EnterpriseId"], name: "EnterpriseId", using: :btree
  add_index "AddressEnterprise", ["NeighborhoodId"], name: "NeighborhoodId", using: :btree
  add_index "AddressEnterprise", ["StateId"], name: "StateId", using: :btree

  create_table "AddressEnterpriseImport", primary_key: "Id", force: :cascade do |t|
    t.integer "AddressId",        limit: 4
    t.integer "EnterpriseId",     limit: 4,   null: false
    t.integer "CityId",           limit: 4
    t.integer "StateId",          limit: 4
    t.string  "Cep",              limit: 10
    t.string  "StreetNameFull",   limit: 200
    t.string  "StreetNumber",     limit: 10
    t.string  "StreetCompletion", limit: 200
    t.integer "NeighborhoodId",   limit: 4
  end

  add_index "AddressEnterpriseImport", ["AddressId"], name: "AddressId", using: :btree
  add_index "AddressEnterpriseImport", ["CityId"], name: "CityId", using: :btree
  add_index "AddressEnterpriseImport", ["EnterpriseId"], name: "EnterpriseId", using: :btree
  add_index "AddressEnterpriseImport", ["StateId"], name: "StateId", using: :btree

  create_table "AddressPresident", primary_key: "Id", force: :cascade do |t|
    t.integer "AddressId",        limit: 4
    t.integer "PresidentId",      limit: 4,   null: false
    t.integer "CityId",           limit: 4
    t.integer "StateId",          limit: 4
    t.string  "Cep",              limit: 10
    t.string  "StreetNameFull",   limit: 200
    t.string  "StreetNumber",     limit: 10
    t.string  "StreetCompletion", limit: 200
    t.integer "NeighborhoodId",   limit: 4
  end

  add_index "AddressPresident", ["AddressId"], name: "AddressId", using: :btree
  add_index "AddressPresident", ["CityId"], name: "CityId", using: :btree
  add_index "AddressPresident", ["NeighborhoodId"], name: "NeighborhoodId", using: :btree
  add_index "AddressPresident", ["PresidentId"], name: "PresidentId", using: :btree
  add_index "AddressPresident", ["StateId"], name: "StateId", using: :btree

  create_table "AddressPresidentImport", primary_key: "Id", force: :cascade do |t|
    t.integer "AddressId",        limit: 4
    t.integer "PresidentId",      limit: 4,   null: false
    t.integer "CityId",           limit: 4
    t.integer "StateId",          limit: 4
    t.string  "Cep",              limit: 10
    t.string  "StreetNameFull",   limit: 200
    t.string  "StreetNumber",     limit: 10
    t.string  "StreetCompletion", limit: 200
    t.integer "NeighborhoodId",   limit: 4
  end

  add_index "AddressPresidentImport", ["AddressId"], name: "AddressId", using: :btree
  add_index "AddressPresidentImport", ["CityId"], name: "CityId", using: :btree
  add_index "AddressPresidentImport", ["PresidentId"], name: "PresidentId", using: :btree
  add_index "AddressPresidentImport", ["StateId"], name: "StateId", using: :btree

  create_table "Alternative", primary_key: "Id", force: :cascade do |t|
    t.integer "AlternativeTypeId",   limit: 4,                              null: false
    t.integer "QuestionId",          limit: 4,                              null: false
    t.integer "ParentAlternativeId", limit: 4
    t.string  "Designation",         limit: 1,                              null: false
    t.text    "Value",               limit: 65535,                          null: false
    t.decimal "Version",                           precision: 10,           null: false
    t.string  "Status",              limit: 1
    t.decimal "ScoreLevel",                        precision: 10, scale: 4
    t.text    "FeedbackDefault",     limit: 65535
    t.text    "DialogueDescription", limit: 65535
  end

  add_index "Alternative", ["AlternativeTypeId"], name: "fk_alternative_2_idx", using: :btree
  add_index "Alternative", ["QuestionId"], name: "fk_alternative_1_idx", using: :btree

  create_table "AlternativeHistory", primary_key: "Id", force: :cascade do |t|
    t.integer "AlternativeId",       limit: 4,                    null: false
    t.string  "Designation",         limit: 1,                    null: false
    t.text    "Description",         limit: 65535,                null: false
    t.decimal "Version",                           precision: 10, null: false
    t.decimal "ScoreLevel",                        precision: 10, null: false
    t.text    "FeedbackDefault",     limit: 65535
    t.text    "DialogueDescription", limit: 65535
    t.date    "LogDate",                                          null: false
  end

  add_index "AlternativeHistory", ["AlternativeId"], name: "fk_alternative_history_1_idx", using: :btree

  create_table "AlternativeType", primary_key: "Id", force: :cascade do |t|
    t.string "Description", limit: 150, null: false
  end

  create_table "AnnualResult", primary_key: "Id", force: :cascade do |t|
    t.integer "QuestionId",    limit: 4,   null: false
    t.integer "AlternativeId", limit: 4,   null: false
    t.string  "Mask",          limit: 45
    t.string  "Value",         limit: 100, null: false
  end

  add_index "AnnualResult", ["AlternativeId"], name: "AlternativeId", using: :btree
  add_index "AnnualResult", ["QuestionId"], name: "QuestionId", using: :btree

  create_table "AnnualResultData", primary_key: "Id", force: :cascade do |t|
    t.integer "AnnualResultId", limit: 4, null: false
    t.integer "AlternativeId",  limit: 4, null: false
    t.integer "Year",           limit: 4, null: false
  end

  add_index "AnnualResultData", ["AlternativeId"], name: "AlternativeId", using: :btree
  add_index "AnnualResultData", ["AnnualResultId"], name: "AnnualResultId", using: :btree

  create_table "Answer", primary_key: "Id", force: :cascade do |t|
    t.integer "AlternativeId", limit: 4,     null: false
    t.text    "AnswerValue",   limit: 65535, null: false
    t.time    "StartTime"
    t.time    "EndTime"
    t.date    "AnswerDate",                  null: false
    t.integer "UserId",        limit: 4
  end

  add_index "Answer", ["AlternativeId"], name: "fk_answer_1_idx", using: :btree

  create_table "AnswerAnnualResult", primary_key: "Id", force: :cascade do |t|
    t.integer "AnnualResultId",     limit: 4,   null: false
    t.integer "AnnualResultDataId", limit: 4,   null: false
    t.integer "AnswerId",           limit: 4,   null: false
    t.string  "Value",              limit: 100, null: false
  end

  add_index "AnswerAnnualResult", ["AnnualResultDataId"], name: "AnnualResultDataId", using: :btree
  add_index "AnswerAnnualResult", ["AnnualResultId"], name: "AnnualResultId", using: :btree
  add_index "AnswerAnnualResult", ["AnswerId"], name: "AnswerId", using: :btree

  create_table "AnswerFeedback", primary_key: "Id", force: :cascade do |t|
    t.integer  "UserId",       limit: 4,     null: false
    t.integer  "AnswerId",     limit: 4,     null: false
    t.text     "Feedback",     limit: 65535, null: false
    t.datetime "FeedbackDate",               null: false
  end

  add_index "AnswerFeedback", ["AnswerId"], name: "AnswerId", using: :btree
  add_index "AnswerFeedback", ["UserId"], name: "UserId", using: :btree

  create_table "AnswerFeedbackImprove", primary_key: "Id", force: :cascade do |t|
    t.integer  "UserId",          limit: 4,     null: false
    t.integer  "AnswerId",        limit: 4,     null: false
    t.text     "FeedbackImprove", limit: 65535, null: false
    t.datetime "FeedbackDate",                  null: false
  end

  add_index "AnswerFeedbackImprove", ["AnswerId"], name: "AnswerId", using: :btree
  add_index "AnswerFeedbackImprove", ["UserId"], name: "UserId", using: :btree

  create_table "AnswerHistory", primary_key: "Id", force: :cascade do |t|
    t.integer "UserId",        limit: 4,     null: false
    t.integer "AnswerId",      limit: 4,     null: false
    t.integer "AlternativeId", limit: 4
    t.text    "AnswerValue",   limit: 65535, null: false
    t.time    "StartTime"
    t.time    "EndTime"
    t.date    "AnswerDate"
    t.date    "LogDate",                     null: false
  end

  add_index "AnswerHistory", ["AlternativeId"], name: "AlternativeId", using: :btree
  add_index "AnswerHistory", ["AnswerId"], name: "fk_answer_history_1_idx", using: :btree
  add_index "AnswerHistory", ["UserId"], name: "UserId", using: :btree

  create_table "ApeEvaluation", primary_key: "Id", force: :cascade do |t|
    t.integer  "AppraiserEnterpriseId", limit: 4,             null: false
    t.integer  "AvaliacaoPerguntaId",   limit: 4,             null: false
    t.string   "Resposta",              limit: 1
    t.datetime "Date",                                        null: false
    t.integer  "Linha1",                limit: 4, default: 0, null: false
    t.integer  "Linha2",                limit: 4, default: 0, null: false
  end

  add_index "ApeEvaluation", ["AppraiserEnterpriseId"], name: "AppEnterprise_idx", using: :btree
  add_index "ApeEvaluation", ["AvaliacaoPerguntaId"], name: "AvaliacaoPergunta_idx", using: :btree

  create_table "ApeEvaluationCopy", id: false, force: :cascade do |t|
    t.integer  "Id",                    limit: 4, default: 0, null: false
    t.integer  "AppraiserEnterpriseId", limit: 4,             null: false
    t.integer  "AvaliacaoPerguntaId",   limit: 4,             null: false
    t.string   "Resposta",              limit: 1
    t.datetime "Date",                                        null: false
    t.integer  "Linha1",                limit: 4, default: 0, null: false
    t.integer  "Linha2",                limit: 4, default: 0, null: false
  end

  create_table "ApeEvaluationCopy22", id: false, force: :cascade do |t|
    t.integer  "Id",                    limit: 4, default: 0, null: false
    t.integer  "AppraiserEnterpriseId", limit: 4,             null: false
    t.integer  "AvaliacaoPerguntaId",   limit: 4,             null: false
    t.string   "Resposta",              limit: 1
    t.datetime "Date",                                        null: false
    t.integer  "Linha1",                limit: 4, default: 0, null: false
    t.integer  "Linha2",                limit: 4, default: 0, null: false
  end

  create_table "ApeEvaluationCopy23", id: false, force: :cascade do |t|
    t.integer  "Id",                    limit: 4, default: 0, null: false
    t.integer  "AppraiserEnterpriseId", limit: 4,             null: false
    t.integer  "AvaliacaoPerguntaId",   limit: 4,             null: false
    t.string   "Resposta",              limit: 1
    t.datetime "Date",                                        null: false
    t.integer  "Linha1",                limit: 4, default: 0, null: false
    t.integer  "Linha2",                limit: 4, default: 0, null: false
  end

  create_table "AppraiserEnterprise", primary_key: "Id", force: :cascade do |t|
    t.string   "AppraiserTypeId", limit: 1,                              default: "1"
    t.integer  "UserId",          limit: 4,                                            null: false
    t.integer  "EnterpriseId",    limit: 4,                                            null: false
    t.integer  "QuestionnaireId", limit: 4
    t.integer  "ProgramaId",      limit: 4
    t.string   "Status",          limit: 1,                              default: "N", null: false
    t.decimal  "Pontos",                        precision: 10, scale: 4, default: 0.0, null: false
    t.text     "Conclusao",       limit: 65535,                                        null: false
    t.text     "Devolutiva",      limit: 65535
    t.datetime "ConclusaoDate"
  end

  add_index "AppraiserEnterprise", ["EnterpriseId"], name: "EnterpriseId", using: :btree
  add_index "AppraiserEnterprise", ["UserId"], name: "UserId", using: :btree

  create_table "AppraiserEnterpriseCopy", id: false, force: :cascade do |t|
    t.integer "Id",              limit: 4,                              default: 0,   null: false
    t.string  "AppraiserTypeId", limit: 1,                              default: "1"
    t.integer "UserId",          limit: 4,                                            null: false
    t.integer "EnterpriseId",    limit: 4,                                            null: false
    t.integer "QuestionnaireId", limit: 4
    t.integer "ProgramaId",      limit: 4
    t.string  "Status",          limit: 1,                              default: "N", null: false
    t.decimal "Pontos",                        precision: 10, scale: 4, default: 0.0, null: false
    t.text    "Conclusao",       limit: 65535,                                        null: false
    t.text    "Devolutiva",      limit: 65535
  end

  create_table "AppraiserEnterpriseCopy22", id: false, force: :cascade do |t|
    t.integer "Id",              limit: 4,                              default: 0,   null: false
    t.string  "AppraiserTypeId", limit: 1,                              default: "1"
    t.integer "UserId",          limit: 4,                                            null: false
    t.integer "EnterpriseId",    limit: 4,                                            null: false
    t.integer "QuestionnaireId", limit: 4
    t.integer "ProgramaId",      limit: 4
    t.string  "Status",          limit: 1,                              default: "N", null: false
    t.decimal "Pontos",                        precision: 10, scale: 4, default: 0.0, null: false
    t.text    "Conclusao",       limit: 65535,                                        null: false
    t.text    "Devolutiva",      limit: 65535
  end

  create_table "AppraiserEnterpriseCopy23", id: false, force: :cascade do |t|
    t.integer "Id",              limit: 4,                              default: 0,   null: false
    t.string  "AppraiserTypeId", limit: 1,                              default: "1"
    t.integer "UserId",          limit: 4,                                            null: false
    t.integer "EnterpriseId",    limit: 4,                                            null: false
    t.integer "QuestionnaireId", limit: 4
    t.integer "ProgramaId",      limit: 4
    t.string  "Status",          limit: 1,                              default: "N", null: false
    t.decimal "Pontos",                        precision: 10, scale: 4, default: 0.0, null: false
    t.text    "Conclusao",       limit: 65535,                                        null: false
    t.text    "Devolutiva",      limit: 65535
  end

  create_table "AppraiserUser", primary_key: "Id", force: :cascade do |t|
    t.integer  "UserId",        limit: 4,                     null: false
    t.string   "Status",        limit: 10, default: "unable"
    t.integer  "ResponsibleId", limit: 4,                     null: false
    t.datetime "CreatedAt",                                   null: false
    t.datetime "UpdatedAt",                                   null: false
  end

  add_index "AppraiserUser", ["Status"], name: "IDX_Status", using: :btree
  add_index "AppraiserUser", ["UserId"], name: "IDX_UserId", using: :btree
  add_index "AppraiserUser", ["UserId"], name: "UQ_UserId", using: :btree

  create_table "AvaliacaoPerguntas", primary_key: "Id", force: :cascade do |t|
    t.integer "Bloco",        limit: 4,     null: false
    t.integer "Criterio",     limit: 4,     null: false
    t.string  "QuestaoLetra", limit: 1,     null: false
    t.text    "Questao",      limit: 65535, null: false
    t.integer "Peso",         limit: 4,     null: false
    t.text    "Topico",       limit: 65535, null: false
  end

  create_table "Blacklist", primary_key: "Id", force: :cascade do |t|
    t.string "Context", limit: 45,  null: false
    t.string "Value",   limit: 255, null: false
  end

  add_index "Blacklist", ["Context", "Value"], name: "Blacklist_UQ", unique: true, using: :btree

  create_table "Block", primary_key: "Id", force: :cascade do |t|
    t.integer "QuestionnaireId", limit: 4,     null: false
    t.integer "Designation",     limit: 4
    t.string  "Value",           limit: 100,   null: false
    t.text    "LongDescription", limit: 65535
    t.text    "ConclusionText",  limit: 65535
  end

  add_index "Block", ["QuestionnaireId"], name: "fk_block_1_idx", using: :btree

  create_table "BlockEnterpreneurGrade", primary_key: "Id", force: :cascade do |t|
    t.integer  "EnterpreneurFeatureId", limit: 4
    t.integer  "CompetitionId",         limit: 4
    t.integer  "UserId",                limit: 4
    t.integer  "QuestionnaireId",       limit: 4
    t.integer  "BlockId",               limit: 4
    t.string   "Description",           limit: 150
    t.float    "Points",                limit: 53
    t.datetime "CreateAt",                          null: false
    t.datetime "UpdatedAt"
  end

  add_index "BlockEnterpreneurGrade", ["EnterpreneurFeatureId", "CompetitionId", "UserId", "QuestionnaireId", "BlockId"], name: "EnterpreneurFeatureId", unique: true, using: :btree
  add_index "BlockEnterpreneurGrade", ["UserId"], name: "UserId", using: :btree

  create_table "CNAE", primary_key: "numero", force: :cascade do |t|
    t.text "descricao", limit: 65535
  end

  add_index "CNAE", ["descricao"], name: "descricao", type: :fulltext

  create_table "CheckerEnterprise", primary_key: "Id", force: :cascade do |t|
    t.integer  "CheckerTypeId",    limit: 4,     default: 1,   null: false
    t.integer  "UserId",           limit: 4,                   null: false
    t.integer  "EnterpriseId",     limit: 4,                   null: false
    t.string   "Status",           limit: 1,     default: "N"
    t.text     "Conclusao",        limit: 65535,               null: false
    t.integer  "ProgramaId",       limit: 4,                   null: false
    t.datetime "ConclusaoDate"
    t.integer  "QtdePontosFortes", limit: 4,     default: 0
    t.text     "Devolutiva",       limit: 65535
  end

  add_index "CheckerEnterprise", ["CheckerTypeId"], name: "IDX_CheckerTypeId", using: :btree
  add_index "CheckerEnterprise", ["EnterpriseId"], name: "IDX_EnterpriseId", using: :btree
  add_index "CheckerEnterprise", ["ProgramaId"], name: "IDX_ProgramaId", using: :btree

  create_table "CheckerEvaluation", primary_key: "Id", force: :cascade do |t|
    t.integer  "CheckerEnterpriseId",     limit: 4,     null: false
    t.datetime "Date",                                  null: false
    t.string   "Resposta",                limit: 1
    t.integer  "QuestionCheckerId",       limit: 4
    t.integer  "CriterionNumber",         limit: 4
    t.text     "Comment",                 limit: 65535
    t.integer  "CheckerEvaluationTypeId", limit: 4,     null: false
  end

  add_index "CheckerEvaluation", ["CheckerEnterpriseId"], name: "CheckerEnterprise_idx", using: :btree
  add_index "CheckerEvaluation", ["QuestionCheckerId"], name: "fk_questio_idx", using: :btree

  create_table "City", primary_key: "Id", force: :cascade do |t|
    t.string  "Name",    limit: 200, null: false
    t.string  "Uf",      limit: 2,   null: false
    t.string  "Cep2",    limit: 15,  null: false
    t.integer "StateId", limit: 4,   null: false
    t.string  "Cep",     limit: 15,  null: false
  end

  add_index "City", ["Name"], name: "NameCidade", using: :btree
  add_index "City", ["StateId"], name: "StateId", using: :btree

  create_table "Competition", primary_key: "Id", force: :cascade do |t|
    t.datetime "StartDate",               null: false
    t.datetime "EndDate",                 null: false
    t.string   "Description", limit: 150
    t.datetime "CreateAt",                null: false
    t.datetime "UpdatedAt",               null: false
  end

  create_table "Configuration", primary_key: "Id", force: :cascade do |t|
    t.string "ConfKey",   limit: 255,   null: false
    t.text   "ConfValue", limit: 65535, null: false
  end

  add_index "Configuration", ["ConfKey"], name: "Chave_UNIQUE", unique: true, using: :btree

  create_table "Contact", primary_key: "Id", force: :cascade do |t|
    t.integer "EnterpriseId",  limit: 4
    t.integer "RegionalId",    limit: 4
    t.integer "ContactTypeId", limit: 4,   null: false
    t.integer "UserId",        limit: 4
    t.string  "Value",         limit: 150, null: false
    t.string  "Plus",          limit: 255
    t.string  "Validation",    limit: 50,  null: false
  end

  add_index "Contact", ["ContactTypeId", "EnterpriseId", "UserId"], name: "un_contact_1_idx", unique: true, using: :btree
  add_index "Contact", ["ContactTypeId"], name: "fk_contact_2_idx", using: :btree
  add_index "Contact", ["EnterpriseId"], name: "fk_contact_1_idx", using: :btree
  add_index "Contact", ["RegionalId"], name: "fk_contact_4_idx", using: :btree
  add_index "Contact", ["UserId"], name: "fk_contact_3_idx", using: :btree

  create_table "ContactType", primary_key: "Id", force: :cascade do |t|
    t.string "Description", limit: 100, null: false
  end

  create_table "ContactUsRecipient", primary_key: "Id", force: :cascade do |t|
    t.string "StateId", limit: 45, null: false
    t.string "UserId",  limit: 45, null: false
  end

  add_index "ContactUsRecipient", ["StateId"], name: "StateId", using: :btree

  create_table "Course", primary_key: "Id", force: :cascade do |t|
    t.integer "Code",              limit: 4
    t.string  "Name",              limit: 255
    t.integer "CourseTypeId",      limit: 4
    t.string  "ManagementThemeId", limit: 255
  end

  add_index "Course", ["Code"], name: "index_Course_on_Code", using: :btree
  add_index "Course", ["Id"], name: "index_Course_on_Id", using: :btree
  add_index "Course", ["Name"], name: "index_Course_on_Name", using: :btree

  create_table "CourseType", primary_key: "Id", force: :cascade do |t|
    t.string "Name", limit: 255
  end

  add_index "CourseType", ["Name"], name: "index_CourseType_on_Name", using: :btree

  create_table "Criterion", primary_key: "Id", force: :cascade do |t|
    t.integer "BlockId",         limit: 4,     null: false
    t.integer "Designation",     limit: 4
    t.string  "Value",           limit: 255,   null: false
    t.text    "LongDescription", limit: 65535
    t.text    "ConclusionText",  limit: 65535
  end

  add_index "Criterion", ["BlockId"], name: "fk_criterion_1_idx", using: :btree

  create_table "DevolutiveCalc", primary_key: "Id", force: :cascade do |t|
    t.string "Title",       limit: 150
    t.text   "Description", limit: 65535
    t.text   "Calculation", limit: 65535
  end

  create_table "Education", primary_key: "Id", force: :cascade do |t|
    t.string "Description", limit: 100, null: false
  end

  create_table "EligibilityHistory", primary_key: "Id", force: :cascade do |t|
    t.integer  "EnterpriseId",    limit: 4, null: false
    t.integer  "QuestionnaireId", limit: 4, null: false
    t.integer  "UserId",          limit: 4
    t.integer  "PremioFlag",      limit: 1
    t.datetime "EligibilityDate",           null: false
    t.boolean  "Eligibility",     limit: 1, null: false
  end

  add_index "EligibilityHistory", ["EnterpriseId"], name: "fk_EligibilityHistory_1_idx", using: :btree
  add_index "EligibilityHistory", ["QuestionnaireId"], name: "fk_EligibilityHistory_1_idx2", using: :btree
  add_index "EligibilityHistory", ["UserId"], name: "fk_EligibilityHistory_1_idx1", using: :btree

  create_table "EmailMessage", primary_key: "Id", force: :cascade do |t|
    t.string   "Context",       limit: 100,        null: false
    t.string   "SenderName",    limit: 100
    t.string   "SenderAddress", limit: 100,        null: false
    t.string   "Subject",       limit: 100
    t.text     "Body",          limit: 4294967295, null: false
    t.string   "Status",        limit: 10
    t.datetime "CreatedAt",                        null: false
    t.datetime "SentAt"
  end

  add_index "EmailMessage", ["Status"], name: "IDX_STATUS", using: :btree

  create_table "EmailQueue", primary_key: "Id", force: :cascade do |t|
    t.string   "From",        limit: 150,                      null: false
    t.string   "To",          limit: 150,                      null: false
    t.string   "Bcc",         limit: 100
    t.string   "Subject",     limit: 200,                      null: false
    t.text     "Message",     limit: 65535
    t.string   "TypeQueue",   limit: 10,    default: "Admin",  null: false
    t.string   "StatusQueue", limit: 10,    default: "ESPERA"
    t.datetime "UpdatedAt"
    t.datetime "CreatedAt"
    t.string   "ErrMsg",      limit: 250
  end

  create_table "EmailRecipient", primary_key: "Id", force: :cascade do |t|
    t.integer "EmailMessageId", limit: 4,  null: false
    t.string  "Name",           limit: 45
    t.string  "Address",        limit: 45, null: false
  end

  add_index "EmailRecipient", ["EmailMessageId", "Name", "Address"], name: "UNIQUE", unique: true, using: :btree
  add_index "EmailRecipient", ["EmailMessageId"], name: "IDX_EmailMessageId", using: :btree

  create_table "EnterpreneurFeatures", primary_key: "Id", force: :cascade do |t|
    t.integer "CompetitionId",         limit: 4,               null: false
    t.integer "EnterpreneurFeatureId", limit: 4,               null: false
    t.string  "Description",           limit: 150,             null: false
    t.integer "Designation",           limit: 4,   default: 0
    t.string  "Questions",             limit: 30
    t.string  "AlgorithmCalc",         limit: 150
  end

  create_table "Enterprise", primary_key: "Id", force: :cascade do |t|
    t.string  "IdKey",                    limit: 40
    t.integer "CategoryAwardId",          limit: 4,                                            null: false
    t.integer "CategorySectorId",         limit: 4,                                            null: false
    t.integer "MetierId",                 limit: 4,                                            null: false
    t.string  "SocialName",               limit: 200,                                          null: false
    t.string  "FantasyName",              limit: 200
    t.string  "Status",                   limit: 1,                                            null: false
    t.string  "Cnpj",                     limit: 19,                                           null: false
    t.string  "StateRegistration",        limit: 50
    t.string  "Dap",                      limit: 50
    t.string  "RegisterMinistryFisher",   limit: 50
    t.string  "OcbRegister",              limit: 30
    t.date    "CreationDate"
    t.date    "CnpjSignupDate"
    t.integer "CooperatedQuantity",       limit: 4
    t.integer "EmployeesQuantity",        limit: 4
    t.integer "AnnualRevenue",            limit: 1
    t.string  "Cnae",                     limit: 30
    t.string  "EmailDefault",             limit: 120
    t.string  "Phone",                    limit: 20
    t.text    "CompanyHistory",           limit: 65535,                                        null: false
    t.string  "CentralName",              limit: 100
    t.string  "HeadOfficeStatus",         limit: 1,                              default: "0"
    t.string  "SingularStatus",           limit: 1,                              default: "0"
    t.string  "FederationName",           limit: 100
    t.string  "ConfederationName",        limit: 100
    t.integer "DiagnosticoEligibility",   limit: 1,                              default: 0
    t.integer "AutoavaliacaoEligibility", limit: 1,                              default: 0
    t.integer "PremioEligibility",        limit: 1,                              default: 0
    t.string  "Site",                     limit: 100,                                          null: false
    t.string  "Nirf",                     limit: 50
    t.decimal "FarmSize",                               precision: 12, scale: 2
    t.boolean "HasntEmail",               limit: 1
  end

  add_index "Enterprise", ["CategorySectorId"], name: "CategorySectorId", using: :btree
  add_index "Enterprise", ["Cnae"], name: "Cnae", using: :btree
  add_index "Enterprise", ["HasntEmail"], name: "index_Enterprise_on_HasntEmail", using: :btree
  add_index "Enterprise", ["IdKey"], name: "IdKey", using: :btree
  add_index "Enterprise", ["MetierId"], name: "MetierId", using: :btree
  add_index "Enterprise", ["SocialName", "FantasyName"], name: "SocialName", using: :btree

  create_table "EnterpriseCategoryAward", primary_key: "Id", force: :cascade do |t|
    t.string "Description", limit: 100, null: false
  end

  create_table "EnterpriseCategoryAwardCompetition", primary_key: "Id", force: :cascade do |t|
    t.integer  "EnterpriseId",              limit: 4,                   null: false
    t.integer  "EnterpriseCategoryAwardId", limit: 4,                   null: false
    t.integer  "CompetitionId",             limit: 4,   default: 2013,  null: false
    t.datetime "CreatedAt"
    t.string   "Token",                     limit: 255
    t.boolean  "Verified",                  limit: 1,   default: false
  end

  add_index "EnterpriseCategoryAwardCompetition", ["CompetitionId", "EnterpriseId"], name: "ecacenterpriseid2", using: :btree
  add_index "EnterpriseCategoryAwardCompetition", ["EnterpriseId", "Verified"], name: "IDX_ECAC_EnterpriseId_Verified", using: :btree
  add_index "EnterpriseCategoryAwardCompetition", ["EnterpriseId"], name: "ecacenterpriseid", using: :btree
  add_index "EnterpriseCategoryAwardCompetition", ["Token"], name: "index_EnterpriseCategoryAwardCompetition_on_Token", using: :btree

  create_table "EnterpriseCategorySector", primary_key: "Id", force: :cascade do |t|
    t.string "Description", limit: 100, null: false
  end

  create_table "EnterpriseImport", primary_key: "Id", force: :cascade do |t|
    t.integer "CategoryAwardId",          limit: 4,                   null: false
    t.integer "CategorySectorId",         limit: 4,                   null: false
    t.integer "MetierId",                 limit: 4,                   null: false
    t.string  "SocialName",               limit: 200,                 null: false
    t.string  "FantasyName",              limit: 200
    t.string  "Status",                   limit: 1,                   null: false
    t.string  "Cnpj",                     limit: 19,                  null: false
    t.string  "StateRegistration",        limit: 50
    t.string  "Dap",                      limit: 50
    t.string  "RegisterMinistryFisher",   limit: 50
    t.string  "OcbRegister",              limit: 30
    t.date    "CreationDate"
    t.date    "CnpjSignupDate"
    t.integer "CooperatedQuantity",       limit: 4
    t.integer "EmployeesQuantity",        limit: 4
    t.integer "AnnualRevenue",            limit: 1
    t.string  "Cnae",                     limit: 30
    t.string  "EmailDefault",             limit: 120
    t.string  "Phone",                    limit: 20
    t.text    "CompanyHistory",           limit: 65535,               null: false
    t.string  "CentralName",              limit: 100
    t.string  "HeadOfficeStatus",         limit: 1,     default: "0"
    t.string  "SingularStatus",           limit: 1,     default: "0"
    t.string  "FederationName",           limit: 100
    t.string  "ConfederationName",        limit: 100
    t.integer "DiagnosticoEligibility",   limit: 1,     default: 0
    t.integer "AutoavaliacaoEligibility", limit: 1,     default: 0
    t.integer "PremioEligibility",        limit: 1,     default: 0
    t.string  "Site",                     limit: 100,                 null: false
  end

  add_index "EnterpriseImport", ["MetierId"], name: "MetierId", using: :btree
  add_index "EnterpriseImport", ["SocialName", "FantasyName"], name: "SocialName", using: :btree

  create_table "EnterpriseProgramaRank", primary_key: "Id", force: :cascade do |t|
    t.string   "EnterpriseIdKey",                    limit: 45,                  null: false
    t.integer  "ProgramaId",                         limit: 4,                   null: false
    t.integer  "UserId",                             limit: 4,                   null: false
    t.string   "Classificar",                        limit: 1,     default: "0"
    t.string   "Desclassificar",                     limit: 1,     default: "0"
    t.text     "Justificativa",                      limit: 65535
    t.datetime "DataInscricao"
    t.string   "ClassificadoVerificacao",            limit: 1,     default: "0"
    t.string   "DesclassificadoVerificacao",         limit: 1,     default: "0"
    t.text     "MotivoDesclassificadoVerificacao",   limit: 65535
    t.string   "ClassificadoOuro",                   limit: 1,     default: "0"
    t.string   "ClassificadoPrata",                  limit: 1,     default: "0"
    t.string   "ClassificadoBronze",                 limit: 1,     default: "0"
    t.string   "DesclassificadoFinal",               limit: 1,     default: "0"
    t.text     "MotivoDesclassificadoFinal",         limit: 65535
    t.string   "ClassificarNacional",                limit: 1,     default: "0"
    t.string   "DesclassificarNacional",             limit: 1,     default: "0"
    t.text     "MotivoDesclassificadoNacional",      limit: 65535
    t.string   "ClassificarFase2Nacional",           limit: 1,     default: "0"
    t.string   "DesclassificarFase2Nacional",        limit: 1,     default: "0"
    t.text     "MotivoDesclassificadoFase2Nacional", limit: 65535
    t.string   "ClassificarFase3Nacional",           limit: 1,     default: "0"
    t.string   "DesclassificarFase3Nacional",        limit: 1,     default: "0"
    t.text     "MotivoDesclassificadoFase3Nacional", limit: 65535
    t.string   "ClassificadoOuroNacional",           limit: 1,     default: "0"
    t.string   "ClassificadoPrataNacional",          limit: 1,     default: "0"
    t.string   "ClassificadoBronzeNacional",         limit: 1,     default: "0"
  end

  add_index "EnterpriseProgramaRank", ["EnterpriseIdKey"], name: "IdKey", using: :btree
  add_index "EnterpriseProgramaRank", ["ProgramaId", "EnterpriseIdKey"], name: "IdKeyE", using: :btree
  add_index "EnterpriseProgramaRank", ["ProgramaId"], name: "ProgramaId", using: :btree
  add_index "EnterpriseProgramaRank", ["UserId"], name: "UserId", using: :btree

  create_table "EnterpriseProgramaRankLog", primary_key: "Id", force: :cascade do |t|
    t.integer  "EnterpriseProgramaRankId",         limit: 4,                   null: false
    t.string   "EnterpriseIdKey",                  limit: 45,                  null: false
    t.integer  "ProgramaId",                       limit: 4,                   null: false
    t.integer  "UserId",                           limit: 4,                   null: false
    t.string   "Classificar",                      limit: 1,     default: "0"
    t.string   "Desclassificar",                   limit: 1,     default: "0"
    t.text     "Justificativa",                    limit: 65535
    t.datetime "DataInscricao"
    t.string   "ClassificadoVerificacao",          limit: 1,     default: "0"
    t.string   "DesclassificadoVerificacao",       limit: 1,     default: "0"
    t.text     "MotivoDesclassificadoVerificacao", limit: 65535
    t.datetime "DateInsert"
  end

  add_index "EnterpriseProgramaRankLog", ["EnterpriseIdKey"], name: "IdKey", using: :btree
  add_index "EnterpriseProgramaRankLog", ["ProgramaId"], name: "ProgramaId", using: :btree
  add_index "EnterpriseProgramaRankLog", ["UserId"], name: "UserId", using: :btree

  create_table "EnterpriseReport", primary_key: "Id", force: :cascade do |t|
    t.integer "EnterpriseId",  limit: 4,        null: false
    t.integer "CompetitionId", limit: 4,        null: false
    t.text    "Report",        limit: 16777215, null: false
    t.string  "Title",         limit: 50,       null: false
  end

  add_index "EnterpriseReport", ["CompetitionId"], name: "CompetitionId", using: :btree
  add_index "EnterpriseReport", ["EnterpriseId"], name: "EnterpriseId", using: :btree

  create_table "EnterpriseReportImport", primary_key: "Id", force: :cascade do |t|
    t.integer "EnterpriseId",  limit: 4,        null: false
    t.integer "CompetitionId", limit: 4,        null: false
    t.text    "Report",        limit: 16777215, null: false
    t.string  "Title",         limit: 50,       null: false
  end

  add_index "EnterpriseReportImport", ["CompetitionId"], name: "CompetitionId", using: :btree
  add_index "EnterpriseReportImport", ["EnterpriseId"], name: "EnterpriseId", using: :btree

  create_table "EnterpriseType", primary_key: "Id", force: :cascade do |t|
    t.string "Description", limit: 100, null: false
  end

  create_table "EnterpriseValuer", id: false, force: :cascade do |t|
    t.integer "ValuerId",     limit: 4, null: false
    t.integer "EnterpriseId", limit: 4, null: false
  end

  add_index "EnterpriseValuer", ["EnterpriseId", "ValuerId"], name: "un_enterprise_valuer_1_idx", unique: true, using: :btree
  add_index "EnterpriseValuer", ["EnterpriseId"], name: "fk_enterprise_valuer_2_idx", using: :btree
  add_index "EnterpriseValuer", ["ValuerId"], name: "fk_enterprise_valuer_1_idx", using: :btree

  create_table "Execution", primary_key: "Id", force: :cascade do |t|
    t.integer  "UserId",          limit: 4,                                             null: false
    t.integer  "ValuerId",        limit: 4
    t.integer  "QuestionnaireId", limit: 4,                                             null: false
    t.datetime "Start"
    t.datetime "Finish"
    t.time     "ExecutionTime",                                                         null: false
    t.string   "Status",          limit: 1,                                             null: false
    t.text     "DevolutivePath",  limit: 65535
    t.text     "EvaluationPath",  limit: 65535
    t.decimal  "FinalScore",                    precision: 10, scale: 4
    t.integer  "AppraiserId",     limit: 4
    t.integer  "Progress",        limit: 4
    t.integer  "ProgramaId",      limit: 4,                              default: 2013, null: false
  end

  add_index "Execution", ["ProgramaId"], name: "ProgramaId", using: :btree
  add_index "Execution", ["QuestionnaireId", "ProgramaId"], name: "un_execution_6_idx", using: :btree
  add_index "Execution", ["QuestionnaireId"], name: "fk_execution_2_idx", using: :btree
  add_index "Execution", ["UserId", "QuestionnaireId"], name: "un_execution_1_idx", using: :btree
  add_index "Execution", ["UserId"], name: "fk_execution_1_idx", using: :btree
  add_index "Execution", ["ValuerId"], name: "fk_execution_3_idx", using: :btree

  create_table "ExecutionPontuacao", primary_key: "Id", force: :cascade do |t|
    t.integer  "ExecutionId",   limit: 4,                          null: false
    t.decimal  "NegociosTotal",           precision: 10, scale: 4
    t.datetime "CreatedAt",                                        null: false
    t.datetime "UpdatedAt"
  end

  add_index "ExecutionPontuacao", ["ExecutionId"], name: "ExecutionId", using: :btree

  create_table "ExecutionPontuacaoLog", primary_key: "Id", force: :cascade do |t|
    t.integer  "ExecutionId",   limit: 4
    t.decimal  "NegociosTotal",           precision: 10, scale: 4
    t.datetime "CreatedAt"
  end

  add_index "ExecutionPontuacaoLog", ["ExecutionId"], name: "index_ExecutionPontuacaoLog_on_ExecutionId", using: :btree

  create_table "Glossary", primary_key: "Id", force: :cascade do |t|
    t.string  "Term",        limit: 45,    null: false
    t.text    "Description", limit: 65535, null: false
    t.integer "TermLen",     limit: 1,     null: false
  end

  add_index "Glossary", ["TermLen"], name: "glossaryIdx", using: :btree

  create_table "Group", primary_key: "Id", force: :cascade do |t|
    t.text   "Description", limit: 65535, null: false
    t.string "Name",        limit: 120
  end

  create_table "GroupEnterprise", primary_key: "Id", force: :cascade do |t|
    t.integer "GroupId",      limit: 4, null: false
    t.integer "EnterpriseId", limit: 4, null: false
  end

  add_index "GroupEnterprise", ["EnterpriseId"], name: "EnterpriseId", using: :btree
  add_index "GroupEnterprise", ["GroupId"], name: "GroupId", using: :btree

  create_table "LogCadastroEmpresa", primary_key: "Id", force: :cascade do |t|
    t.integer  "UserIdLog",    limit: 4,  null: false
    t.integer  "EnterpriseId", limit: 4,  null: false
    t.integer  "ProgramaId",   limit: 4
    t.string   "Acao",         limit: 45
    t.datetime "CriadoEm",                null: false
  end

  add_index "LogCadastroEmpresa", ["EnterpriseId"], name: "EnterpriseId", using: :btree
  add_index "LogCadastroEmpresa", ["ProgramaId"], name: "ProgramaId", using: :btree
  add_index "LogCadastroEmpresa", ["UserIdLog"], name: "UserIdLog", using: :btree

  create_table "LogCadastroEmpresaBkp", primary_key: "Id", force: :cascade do |t|
    t.integer  "UserIdLog",    limit: 4,  null: false
    t.integer  "EnterpriseId", limit: 4,  null: false
    t.integer  "ProgramaId",   limit: 4
    t.string   "Acao",         limit: 45
    t.datetime "CriadoEm",                null: false
  end

  add_index "LogCadastroEmpresaBkp", ["EnterpriseId"], name: "EnterpriseId", using: :btree
  add_index "LogCadastroEmpresaBkp", ["ProgramaId"], name: "ProgramaId", using: :btree
  add_index "LogCadastroEmpresaBkp", ["UserIdLog"], name: "UserIdLog", using: :btree

  create_table "ManagementTheme", primary_key: "Id", force: :cascade do |t|
    t.string "Name", limit: 100, null: false
  end

  add_index "ManagementTheme", ["Name"], name: "Name_UNIQUE", unique: true, using: :btree

  create_table "ManagementThemeQuestion", primary_key: "Id", force: :cascade do |t|
    t.integer "ManagementThemeId", limit: 4,                         null: false
    t.integer "QuestionId",        limit: 4,                         null: false
    t.decimal "QuestionWeight",              precision: 5, scale: 2, null: false
  end

  add_index "ManagementThemeQuestion", ["ManagementThemeId", "QuestionId"], name: "UQ_ManagementThemeQuestion", unique: true, using: :btree
  add_index "ManagementThemeQuestion", ["ManagementThemeId"], name: "IDX_ManagementTheme", using: :btree
  add_index "ManagementThemeQuestion", ["QuestionId"], name: "IDX_Question", using: :btree

  create_table "Metier", primary_key: "Id", force: :cascade do |t|
    t.string  "Description", limit: 200, null: false
    t.integer "Classe",      limit: 4
  end

  create_table "Neighborhood", primary_key: "Id", force: :cascade do |t|
    t.string  "Uf",     limit: 2,   null: false
    t.integer "CityId", limit: 4,   null: false
    t.string  "Name",   limit: 200, null: false
  end

  add_index "Neighborhood", ["CityId"], name: "City", using: :btree
  add_index "Neighborhood", ["Name"], name: "NameBairro", using: :btree

  create_table "Position", primary_key: "Id", force: :cascade do |t|
    t.string "Description", limit: 100, null: false
  end

  create_table "President", primary_key: "Id", force: :cascade do |t|
    t.integer "EnterpriseId",    limit: 4,   null: false
    t.integer "EducationId",     limit: 4
    t.integer "PositionId",      limit: 4,   null: false
    t.integer "FindUsId",        limit: 4
    t.string  "Name",            limit: 100
    t.string  "NickName",        limit: 100, null: false
    t.string  "Cpf",             limit: 14
    t.string  "Email",           limit: 100
    t.string  "Phone",           limit: 45
    t.string  "Cellphone",       limit: 20,  null: false
    t.date    "BornDate"
    t.string  "Gender",          limit: 1
    t.date    "StartMandate"
    t.date    "EndMandate"
    t.string  "NewsletterEmail", limit: 1,   null: false
    t.string  "NewsletterMail",  limit: 1,   null: false
    t.string  "NewsletterSms",   limit: 1,   null: false
    t.string  "Agree",           limit: 1,   null: false
    t.date    "Created"
  end

  add_index "President", ["Cpf"], name: "Cpf", using: :btree
  add_index "President", ["EducationId"], name: "EducationId", using: :btree
  add_index "President", ["EnterpriseId", "EducationId"], name: "EnterpriseId", using: :btree
  add_index "President", ["PositionId"], name: "PositionId", using: :btree

  create_table "PresidentImport", primary_key: "Id", force: :cascade do |t|
    t.integer  "EnterpriseId",    limit: 4,   null: false
    t.integer  "EducationId",     limit: 4
    t.integer  "PositionId",      limit: 4,   null: false
    t.integer  "FindUsId",        limit: 4
    t.string   "Name",            limit: 100
    t.string   "NickName",        limit: 100, null: false
    t.string   "Cpf",             limit: 11
    t.string   "Email",           limit: 100
    t.string   "Phone",           limit: 45
    t.string   "Cellphone",       limit: 20,  null: false
    t.date     "BornDate"
    t.string   "Gender",          limit: 1
    t.date     "StartMandate"
    t.date     "EndMandate"
    t.string   "NewsletterEmail", limit: 1,   null: false
    t.string   "NewsletterMail",  limit: 1,   null: false
    t.string   "NewsletterSms",   limit: 1,   null: false
    t.string   "Agree",           limit: 1,   null: false
    t.datetime "Created"
  end

  add_index "PresidentImport", ["EnterpriseId", "EducationId"], name: "EnterpriseId", using: :btree
  add_index "PresidentImport", ["PositionId"], name: "PositionId", using: :btree

  create_table "PresidentProgram", primary_key: "Id", force: :cascade do |t|
    t.integer "PresidentId",            limit: 4,                null: false
    t.integer "PresidentProgramTypeId", limit: 4,                null: false
    t.integer "CompetitionId",          limit: 4, default: 2013, null: false
  end

  add_index "PresidentProgram", ["PresidentId"], name: "PresidentId", using: :btree

  create_table "PresidentProgramType", primary_key: "Id", force: :cascade do |t|
    t.string "Description", limit: 100, null: false
    t.string "ElementName", limit: 50,  null: false
  end

  create_table "PrivilegeLongDescription", primary_key: "Id", force: :cascade do |t|
    t.integer "ResourceId",      limit: 4,   null: false
    t.string  "Privilege",       limit: 60,  null: false
    t.string  "LongDescription", limit: 100
  end

  add_index "PrivilegeLongDescription", ["Privilege"], name: "Privilege", using: :btree
  add_index "PrivilegeLongDescription", ["ResourceId"], name: "ResourceId", using: :btree

  create_table "ProtocoloDevolutiva", primary_key: "Id", force: :cascade do |t|
    t.integer  "UserId",         limit: 4,   null: false
    t.integer  "UserIdLogado",   limit: 4,   null: false
    t.integer  "ProgramaId",     limit: 4,   null: false
    t.string   "DevolutivePath", limit: 250
    t.datetime "CreateAt",                   null: false
  end

  add_index "ProtocoloDevolutiva", ["DevolutivePath"], name: "DevolutivePath", using: :btree
  add_index "ProtocoloDevolutiva", ["UserId"], name: "UserId", using: :btree

  create_table "Question", primary_key: "Id", force: :cascade do |t|
    t.integer "QuestionTypeId",   limit: 4,                    null: false
    t.integer "CriterionId",      limit: 4,                    null: false
    t.integer "ParentQuestionId", limit: 4
    t.integer "Designation",      limit: 4,                    null: false
    t.text    "Value",            limit: 65535,                null: false
    t.text    "SupportingText",   limit: 65535
    t.decimal "Version",                        precision: 10
    t.string  "Status",           limit: 1,                    null: false
    t.text    "Summary",          limit: 65535
  end

  add_index "Question", ["CriterionId"], name: "fk_question_2_idx", using: :btree
  add_index "Question", ["QuestionTypeId"], name: "fk_question_1_idx", using: :btree

  create_table "QuestionChecker", primary_key: "Id", force: :cascade do |t|
    t.integer "QuestionTypeId", limit: 4,     null: false
    t.integer "Designation",    limit: 4,     null: false
    t.text    "Value",          limit: 65535, null: false
    t.string  "Status",         limit: 1,     null: false
  end

  add_index "QuestionChecker", ["QuestionTypeId", "Designation"], name: "fk_questionChecker_1_idx", using: :btree

  create_table "QuestionHistory", primary_key: "Id", force: :cascade do |t|
    t.integer  "QuestionId",  limit: 4,                    null: false
    t.string   "Designation", limit: 100,                  null: false
    t.text     "Value",       limit: 65535,                null: false
    t.decimal  "Version",                   precision: 10, null: false
    t.datetime "LogDate",                                  null: false
  end

  add_index "QuestionHistory", ["QuestionId"], name: "fk_questions_log_questions1_idx", using: :btree

  create_table "QuestionTip", primary_key: "Id", force: :cascade do |t|
    t.integer "QuestionId",        limit: 4,     null: false
    t.integer "QuestionTipTypeId", limit: 4
    t.text    "Value",             limit: 65535
  end

  add_index "QuestionTip", ["QuestionId"], name: "fk_question_tips_1_idx", using: :btree
  add_index "QuestionTip", ["QuestionTipTypeId"], name: "QuestionTipTypeId", using: :btree

  create_table "QuestionTipType", primary_key: "Id", force: :cascade do |t|
    t.string "title",       limit: 20,  null: false
    t.string "Description", limit: 100, null: false
  end

  create_table "QuestionType", primary_key: "Id", force: :cascade do |t|
    t.string "Description", limit: 150, null: false
  end

  create_table "Questionnaire", primary_key: "Id", force: :cascade do |t|
    t.integer "CompetitionId",              limit: 4
    t.string  "Title",                      limit: 150,                  null: false
    t.string  "Description",                limit: 150,                  null: false
    t.text    "LongDescription",            limit: 65535,                null: false
    t.decimal "Version",                                  precision: 10
    t.date    "OperationBeginning",                                      null: false
    t.date    "OperationEnding",                                         null: false
    t.integer "DevolutiveCalcId",           limit: 4
    t.date    "PublicSubscriptionEndsAt"
    t.date    "InternalSubscriptionEndsAt"
  end

  create_table "Regiao", primary_key: "Id", force: :cascade do |t|
    t.string "Nome",      limit: 45
    t.string "Descricao", limit: 45
  end

  add_index "Regiao", ["Descricao"], name: "Descricao", using: :btree
  add_index "Regiao", ["Nome"], name: "Nome", using: :btree

  create_table "Regional", primary_key: "Id", force: :cascade do |t|
    t.string  "Description", limit: 255,               null: false
    t.string  "Status",      limit: 1,   default: "A", null: false
    t.string  "National",    limit: 1,   default: "N"
    t.string  "Estadual",    limit: 2
    t.integer "RegiaoId",    limit: 4,   default: 1
  end

  add_index "Regional", ["Description"], name: "Description_UNIQUE", unique: true, using: :btree
  add_index "Regional", ["Id", "Status"], name: "IdAndStatus", using: :btree
  add_index "Regional", ["Status"], name: "Status", using: :btree

  create_table "RegionalTest", primary_key: "Id", force: :cascade do |t|
    t.string  "Description", limit: 255,               null: false
    t.string  "Status",      limit: 1,   default: "A", null: false
    t.string  "National",    limit: 1,   default: "N"
    t.string  "Estadual",    limit: 2
    t.integer "RegiaoId",    limit: 4,   default: 1
  end

  add_index "RegionalTest", ["Description"], name: "Description_UNIQUE", unique: true, using: :btree

  create_table "Resource", primary_key: "Id", force: :cascade do |t|
    t.string  "Module",               limit: 30,  null: false
    t.string  "Description",          limit: 50,  null: false
    t.string  "LongDescription",      limit: 120, null: false
    t.integer "ParentResource",       limit: 4
    t.string  "LongDescriptionAlias", limit: 120
  end

  add_index "Resource", ["Description", "Module"], name: "uc_resource", unique: true, using: :btree
  add_index "Resource", ["ParentResource"], name: "idx_resource_parent", using: :btree

  create_table "Role", primary_key: "Id", force: :cascade do |t|
    t.string  "Description",     limit: 100,               null: false
    t.string  "LongDescription", limit: 100,               null: false
    t.integer "ParentRole",      limit: 4
    t.string  "IsSystemAdmin",   limit: 1,   default: "0", null: false
    t.string  "IsSystemRole",    limit: 1,   default: "0", null: false
  end

  add_index "Role", ["ParentRole"], name: "fk_role_1_idx", using: :btree

  create_table "RoleQuestionnaire", primary_key: "Id", force: :cascade do |t|
    t.integer "RoleId",          limit: 4, null: false
    t.integer "QuestionnaireId", limit: 4, null: false
    t.date    "StartDate",                 null: false
    t.date    "EndDate",                   null: false
  end

  add_index "RoleQuestionnaire", ["QuestionnaireId"], name: "QuestionnaireId", using: :btree
  add_index "RoleQuestionnaire", ["RoleId"], name: "RoleId", using: :btree

  create_table "Role_Resource_Privilege", primary_key: "Id", force: :cascade do |t|
    t.integer "RoleId",     limit: 4,  null: false
    t.integer "ResourceId", limit: 4,  null: false
    t.string  "Privilege",  limit: 60, null: false
  end

  add_index "Role_Resource_Privilege", ["ResourceId"], name: "fk_r_r_p_2_idx", using: :btree
  add_index "Role_Resource_Privilege", ["RoleId"], name: "fk_r_r_p_1_idx", using: :btree

  create_table "Senhas", id: false, force: :cascade do |t|
    t.integer "EnterpriseId", limit: 4,  null: false
    t.string  "Cpf",          limit: 11, null: false
    t.string  "Password",     limit: 64, null: false
  end

  create_table "ServiceArea", primary_key: "Id", force: :cascade do |t|
    t.integer "RegionalId",     limit: 4, null: false
    t.integer "StateId",        limit: 4
    t.integer "CityId",         limit: 4
    t.integer "NeighborhoodId", limit: 4
  end

  add_index "ServiceArea", ["CityId"], name: "fk_service_area_2_idx", using: :btree
  add_index "ServiceArea", ["CityId"], name: "idx_CityId", using: :btree
  add_index "ServiceArea", ["NeighborhoodId", "RegionalId"], name: "regional_neigh", using: :btree
  add_index "ServiceArea", ["NeighborhoodId"], name: "fk_ServiceArea_1_idx", using: :btree
  add_index "ServiceArea", ["NeighborhoodId"], name: "idx_NeighborhoordId", using: :btree
  add_index "ServiceArea", ["RegionalId", "CityId"], name: "regional_city", using: :btree
  add_index "ServiceArea", ["RegionalId", "CityId"], name: "un_service_area_1_idx", unique: true, using: :btree
  add_index "ServiceArea", ["RegionalId", "StateId"], name: "regional_state", using: :btree
  add_index "ServiceArea", ["RegionalId"], name: "fk_service_area_1_idx", using: :btree
  add_index "ServiceArea", ["StateId", "CityId", "NeighborhoodId"], name: "idx_StateId_CityId_NeighborhoordId", using: :btree
  add_index "ServiceArea", ["StateId"], name: "fk_service_area_4_idx", using: :btree
  add_index "ServiceArea", ["StateId"], name: "idx_StateId", using: :btree

  create_table "ServiceAreaCache", primary_key: "Id", force: :cascade do |t|
    t.integer "RegionalId",     limit: 4
    t.integer "StateId",        limit: 4
    t.integer "CityId",         limit: 4
    t.integer "NeighborhoodId", limit: 4
  end

  add_index "ServiceAreaCache", ["CityId"], name: "index_ServiceAreaCache_on_CityId", using: :btree
  add_index "ServiceAreaCache", ["NeighborhoodId"], name: "index_ServiceAreaCache_on_NeighborhoodId", using: :btree
  add_index "ServiceAreaCache", ["RegionalId"], name: "index_ServiceAreaCache_on_RegionalId", using: :btree
  add_index "ServiceAreaCache", ["StateId"], name: "index_ServiceAreaCache_on_StateId", using: :btree

  create_table "State", primary_key: "Id", force: :cascade do |t|
    t.string  "Name", limit: 150, null: false
    t.string  "Uf",   limit: 2,   null: false
    t.integer "Ibge", limit: 4,   null: false
  end

  add_index "State", ["Name"], name: "NameState", using: :btree

  create_table "StateManagerEmail", primary_key: "Id", force: :cascade do |t|
    t.integer "StateId", limit: 4
    t.string  "Email",   limit: 255
  end

  add_index "StateManagerEmail", ["StateId"], name: "index_StateManagerEmail_on_StateId", using: :btree

  create_table "User", primary_key: "Id", force: :cascade do |t|
    t.integer "PositionId",   limit: 4
    t.integer "EducationId",  limit: 4
    t.string  "FirstName",    limit: 100, null: false
    t.string  "Surname",      limit: 100
    t.string  "Login",        limit: 150, null: false
    t.string  "Email",        limit: 120
    t.string  "Keypass",      limit: 128, null: false
    t.string  "Salt",         limit: 15
    t.string  "Status",       limit: 1,   null: false
    t.string  "Cpf",          limit: 14
    t.date    "BornDate"
    t.string  "Gender",       limit: 1
    t.string  "PasswordHint", limit: 250
  end

  add_index "User", ["EducationId"], name: "EducationId", using: :btree
  add_index "User", ["Login"], name: "un_user_1_idx", unique: true, using: :btree
  add_index "User", ["PositionId"], name: "PositionId", using: :btree
  add_index "User", ["PositionId"], name: "fk_user_2_idx", using: :btree

  create_table "UserLocality", primary_key: "Id", force: :cascade do |t|
    t.integer "UserId",       limit: 4, null: false
    t.integer "EnterpriseId", limit: 4
    t.integer "RegionalId",   limit: 4
  end

  add_index "UserLocality", ["EnterpriseId"], name: "fk_user_locality_1_idx", using: :btree
  add_index "UserLocality", ["RegionalId"], name: "fk_user_locality_3_idx", using: :btree
  add_index "UserLocality", ["UserId", "EnterpriseId"], name: "un_user_locality_1_idx", unique: true, using: :btree
  add_index "UserLocality", ["UserId"], name: "fk_user_id_idx", using: :btree

  create_table "User_Role", primary_key: "Id", force: :cascade do |t|
    t.integer "UserId", limit: 4, null: false
    t.integer "RoleId", limit: 4, null: false
  end

  add_index "User_Role", ["RoleId"], name: "fk_user_role_2_idx", using: :btree
  add_index "User_Role", ["UserId"], name: "fk_user_role_1_idx", using: :btree

  create_table "Whitelist", primary_key: "Id", force: :cascade do |t|
    t.string "Context", limit: 45,  null: false
    t.string "Value",   limit: 255, null: false
  end

  add_index "Whitelist", ["Context", "Value"], name: "Blacklist_UQ", unique: true, using: :btree

  create_table "WinningNotification", primary_key: "Id", force: :cascade do |t|
    t.integer  "EmailMessageId", limit: 4, null: false
    t.integer  "CompetitionId",  limit: 4, null: false
    t.integer  "StateId",        limit: 4, null: false
    t.integer  "ResponsibleId",  limit: 4, null: false
    t.datetime "CreatedAt",                null: false
  end

  create_table "WinningNotificationEnterprise", primary_key: "Id", force: :cascade do |t|
    t.integer "WinningNotificationId", limit: 4, null: false
    t.integer "EnterpriseId",          limit: 4, null: false
  end

  create_table "logerro", force: :cascade do |t|
    t.string   "erro",       limit: 50
    t.text     "valor",      limit: 65535
    t.datetime "created_at",               null: false
  end

  create_table "logpontuacao", primary_key: "Id", force: :cascade do |t|
    t.integer  "QuestionId",            limit: 4
    t.integer  "UserId",                limit: 4
    t.integer  "QuestionnaireId",       limit: 4
    t.integer  "BlockId",               limit: 4
    t.integer  "EnterpreneurFeatureId", limit: 4
    t.datetime "createAt",                        null: false
  end

  add_index "logpontuacao", ["QuestionId"], name: "QuestionId", using: :btree
  add_index "logpontuacao", ["UserId"], name: "UserId", using: :btree

  create_table "testev", force: :cascade do |t|
    t.string "nome", limit: 10
  end

  create_table "vw_ServiceArea", id: false, force: :cascade do |t|
    t.integer "RegionalId",     limit: 4, null: false
    t.integer "StateId",        limit: 4
    t.integer "CityId",         limit: 8
    t.integer "NeighborhoodId", limit: 8
  end

  create_table "vw_UserRegional", id: false, force: :cascade do |t|
    t.integer "UserId",         limit: 4, default: 0, null: false
    t.integer "RegionalId",     limit: 4,             null: false
    t.integer "StateId",        limit: 4
    t.integer "CityId",         limit: 8
    t.integer "NeighborhoodId", limit: 8
  end

  add_foreign_key "Alternative", "AlternativeType", column: "AlternativeTypeId", primary_key: "Id", name: "fk_alternative_2"
  add_foreign_key "Alternative", "Question", column: "QuestionId", primary_key: "Id", name: "fk_alternative_1"
  add_foreign_key "AlternativeHistory", "Alternative", column: "AlternativeId", primary_key: "Id", name: "fk_alternative_history_1"
  add_foreign_key "AnnualResult", "Question", column: "QuestionId", primary_key: "Id", name: "AnnualResult_ibfk_1"
  add_foreign_key "Answer", "Alternative", column: "AlternativeId", primary_key: "Id", name: "fk_answer_3"
  add_foreign_key "AnswerAnnualResult", "AnnualResult", column: "AnnualResultId", primary_key: "Id", name: "AnswerAnnualResult_ibfk_1"
  add_foreign_key "AnswerFeedback", "Answer", column: "AnswerId", primary_key: "Id", name: "AnswerFeedback_ibfk_2"
  add_foreign_key "AnswerFeedback", "User", column: "UserId", primary_key: "Id", name: "AnswerFeedback_ibfk_1"
  add_foreign_key "AnswerFeedbackImprove", "Answer", column: "AnswerId", primary_key: "Id", name: "AnswerFeedbackImprove_ibfk_2"
  add_foreign_key "AnswerFeedbackImprove", "User", column: "UserId", primary_key: "Id", name: "AnswerFeedbackImprove_ibfk_1"
  add_foreign_key "AnswerHistory", "Answer", column: "AnswerId", primary_key: "Id", name: "fk_answer_history_1"
  add_foreign_key "AnswerHistory", "User", column: "UserId", primary_key: "Id", name: "AnswerHistory_ibfk_1"
  add_foreign_key "AppraiserEnterprise", "Enterprise", column: "EnterpriseId", primary_key: "Id", name: "AppraiserEnterprise_ibfk_2"
  add_foreign_key "AppraiserEnterprise", "User", column: "UserId", primary_key: "Id", name: "AppraiserEnterprise_ibfk_1"
  add_foreign_key "Block", "Questionnaire", column: "QuestionnaireId", primary_key: "Id", name: "fk_block_1"
  add_foreign_key "CheckerEvaluation", "QuestionChecker", column: "QuestionCheckerId", primary_key: "Id", name: "fk_questio"
  add_foreign_key "Contact", "ContactType", column: "ContactTypeId", primary_key: "Id", name: "fk_contact_2"
  add_foreign_key "Contact", "Enterprise", column: "EnterpriseId", primary_key: "Id", name: "fk_contact_1"
  add_foreign_key "Contact", "Regional", column: "RegionalId", primary_key: "Id", name: "fk_contact_4"
  add_foreign_key "Contact", "User", column: "UserId", primary_key: "Id", name: "fk_contact_3"
  add_foreign_key "Criterion", "Block", column: "BlockId", primary_key: "Id", name: "fk_criterion_1"
  add_foreign_key "EnterpriseValuer", "Enterprise", column: "EnterpriseId", primary_key: "Id", name: "fk_enterprise_valuer_2"
  add_foreign_key "EnterpriseValuer", "User", column: "ValuerId", primary_key: "Id", name: "fk_enterprise_valuer_1"
  add_foreign_key "Execution", "Questionnaire", column: "QuestionnaireId", primary_key: "Id", name: "fk_execution_2"
  add_foreign_key "Execution", "User", column: "UserId", primary_key: "Id", name: "fk_execution_1"
  add_foreign_key "Execution", "User", column: "ValuerId", primary_key: "Id", name: "fk_execution_3"
  add_foreign_key "GroupEnterprise", "Enterprise", column: "EnterpriseId", primary_key: "Id", name: "GroupEnterprise_ibfk_2"
  add_foreign_key "GroupEnterprise", "Group", column: "GroupId", primary_key: "Id", name: "GroupEnterprise_ibfk_1"
  add_foreign_key "PresidentProgram", "President", column: "PresidentId", primary_key: "Id", name: "PresidentProgram_ibfk_1"
  add_foreign_key "Question", "Criterion", column: "CriterionId", primary_key: "Id", name: "Question_ibfk_2"
  add_foreign_key "Question", "QuestionType", column: "QuestionTypeId", primary_key: "Id", name: "Question_ibfk_1"
  add_foreign_key "QuestionChecker", "QuestionType", column: "QuestionTypeId", primary_key: "Id", name: "QuestionChecker_ibfk_1"
  add_foreign_key "QuestionHistory", "Question", column: "QuestionId", primary_key: "Id", name: "fk_question_history_1"
  add_foreign_key "QuestionTip", "Question", column: "QuestionId", primary_key: "Id", name: "fk_question_tips_1"
  add_foreign_key "QuestionTip", "QuestionTipType", column: "QuestionTipTypeId", primary_key: "Id", name: "QuestionTip_ibfk_1"
  add_foreign_key "Resource", "Resource", column: "ParentResource", primary_key: "Id", name: "fk_resources"
  add_foreign_key "Role", "Role", column: "ParentRole", primary_key: "Id", name: "fk_role_1"
  add_foreign_key "RoleQuestionnaire", "Questionnaire", column: "QuestionnaireId", primary_key: "Id", name: "RoleQuestionnaire_ibfk_2"
  add_foreign_key "RoleQuestionnaire", "Role", column: "RoleId", primary_key: "Id", name: "RoleQuestionnaire_ibfk_1"
  add_foreign_key "Role_Resource_Privilege", "Resource", column: "ResourceId", primary_key: "Id", name: "fk_r_r_p_2"
  add_foreign_key "Role_Resource_Privilege", "Role", column: "RoleId", primary_key: "Id", name: "fk_r_r_p_1"
  add_foreign_key "ServiceArea", "City", column: "CityId", primary_key: "Id", name: "fk_service_area_2"
  add_foreign_key "ServiceArea", "Neighborhood", column: "NeighborhoodId", primary_key: "Id", name: "fk_service_area_3"
  add_foreign_key "ServiceArea", "Regional", column: "RegionalId", primary_key: "Id", name: "fk_service_area_1"
  add_foreign_key "ServiceArea", "State", column: "StateId", primary_key: "Id", name: "fk_service_area_4"
  add_foreign_key "User", "Education", column: "EducationId", primary_key: "Id", name: "fk_user_1"
  add_foreign_key "User", "Position", column: "PositionId", primary_key: "Id", name: "fk_user_2"
  add_foreign_key "UserLocality", "Enterprise", column: "EnterpriseId", primary_key: "Id", name: "fk_user_locality_1"
  add_foreign_key "UserLocality", "Regional", column: "RegionalId", primary_key: "Id", name: "fk_user_locality_4"
  add_foreign_key "UserLocality", "User", column: "UserId", primary_key: "Id", name: "fk_user_locality_2"
  add_foreign_key "User_Role", "Role", column: "RoleId", primary_key: "Id", name: "fk_user_role_2"
  add_foreign_key "User_Role", "User", column: "UserId", primary_key: "Id", name: "fk_user_role_1"
end
