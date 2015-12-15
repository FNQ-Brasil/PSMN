class CreateExecutionPontuacaoLog < ActiveRecord::Migration
  def change
    create_table 'ExecutionPontuacaoLog', primary_key: 'Id' do |t|
      t.integer 'ExecutionId'
      t.decimal 'NegociosTotal', precision: 10, scale: 4
      t.timestamp 'CreatedAt'
    end

    add_index 'ExecutionPontuacaoLog', 'ExecutionId'
  end
end