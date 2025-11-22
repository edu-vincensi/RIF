<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateSolicitacaoAddAlunoId extends Migration
{
    public function up()
    {
        // Remove a chave estrangeira existente que referencia turma_id
        $this->db->query('ALTER TABLE solicitacao_refeicoes DROP FOREIGN KEY solicitacao_refeicoes_turma_id_foreign');

        // Removi turma_id e adicionei aluno_id na tabela solicitacao_refeicoes
        $this->forge->dropColumn('solicitacao_refeicoes', 'turma_id');

        // Adiciona a coluna aluno_id
        $this->forge->addColumn('solicitacao_refeicoes', [
            'aluno_id' => [
                'type'       => 'BIGINT', // Usando BIGINT para suportar o ID matricula do aluno
                'constraint' => 11,
                'null'      => false,
                'after'      => 'id'
            ]
        ]);
    }

    public function down()
    {
        // Reverte as mudanças: remove aluno_id e adiciona turma_id de volta
        $this->forge->dropColumn('solicitacao_refeicoes', 'aluno_id');

        // 2) Adiciona turma_id de volta
        $this->forge->addColumn('solicitacao_refeicoes', [
            'turma_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'after'      => 'id'
            ]
        ]);

        // 3) Restaura a foreign key original
        $this->db->query('ALTER TABLE solicitacao_refeicoes 
            ADD CONSTRAINT solicitacao_refeicoes_turma_id_foreign 
            FOREIGN KEY (turma_id) REFERENCES turmas(id)');
    }
}
