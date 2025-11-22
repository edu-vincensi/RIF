<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateSolicitacaoAddMotivo extends Migration
{
    public function up()
    {
        // Remove campo justificativa
        $this->forge->dropColumn('solicitacao_refeicoes', 'justificativa');

        // Adiciona motivo (int)
        $this->forge->addColumn('solicitacao_refeicoes', [
            'motivo' => [
                'type'       => 'INT',
                'constraint' => 1, // 0 a 4 (1 dígito)
                'null'       => false,
                'after'      => 'codigo'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('solicitacao_refeicoes', 'motivo');

        $this->forge->addColumn('solicitacao_refeicoes', [
            'justificativa' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ]
        ]);
    }
}
