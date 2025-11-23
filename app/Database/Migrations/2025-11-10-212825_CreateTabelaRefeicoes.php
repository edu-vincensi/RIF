<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateSolicitacaoRefeicoes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('solicitacao_refeicoes', [
            'data_solicitada' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'id_creat' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
        ]);

        $this->forge->addForeignKey('id_creat', 'users', 'id', 'RESTRICT', 'RESTRICT');
    }

    public function down()
    {

        $this->forge->dropForeignKey('solicitacao_refeicoes', 'id_creat');

        $this->forge->dropColumn('solicitacao_refeicoes', 'data_solicitada');
        $this->forge->dropColumn('solicitacao_refeicoes', 'id_creat');
    }
}