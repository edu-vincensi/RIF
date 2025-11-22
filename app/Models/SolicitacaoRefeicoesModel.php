<?php

namespace App\Models;

use CodeIgniter\Model;

class SolicitacaoRefeicoesModel extends Model
{
    protected $table            = 'solicitacao_refeicoes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['aluno_id', 'data_refeicao', 'crc', 'status', 'codigo', 'motivo', 'id_creat', 'data_solicitada'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules = [
        'id' => 'permit_empty|is_natural_no_zero|max_length[11]',
        'aluno_id' => 'required|is_natural_no_zero',
        'data_refeicao' => 'required|date',
        'crc' => 'required|string',
        'status' => 'permit_empty|numeric',
        'codigo' => 'required|numeric',
        'motivo' => 'required|in_list[0,1,2,3,4]',
    ];

    protected $validationMessages = [
        'aluno_id' => [
            'required' => 'Informe a turma.',
            'is_natural_no_zero' => 'Informe um valor válido.',
        ],
        'data_refeicao' => [
            'required' => 'Informe a data da refeição.',
            'date' => 'Informe uma data válida.'
        ],
        'crc' => [
            'required' => 'Informe o crc da solicitação.',
        ],
        'status' => [
            'required' => 'Informe o status da solicitação.',
        ],
        'codigo' => [
            'required' => 'Informe o código da solicitação.',
        ],
        'motivo' => [
            'required' => 'Informe o motivo',
            'in_list' => 'Selecione um motivo válido.',
        ],
    ];

    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
