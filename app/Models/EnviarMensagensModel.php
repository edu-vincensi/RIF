<?php

namespace App\Models;

use CodeIgniter\Model;

class EnviarMensagensModel extends Model
{
    protected $table            = 'envia_mensagens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['destinatario', 'mensagem', 'status', 'data_envio', 'data_cadastro', 'categoria'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'data_cadastro';
    protected $updatedField  = '';
    protected $deletedField  = '';

    // Validation
    protected $validationRules      = [
        'id'            => 'permit_empty|is_natural_no_zero|max_length[11]',
        'destinatario'  => 'required|max_length[11]',
        'mensagem'      => 'required',
        'status'        => 'required|in_list[0,1]',
        'data_cadastro' => 'permit_empty|valid_date',
        'categoria'     => 'required',
    ];
    
    protected $validationMessages   = [
        'destinatario' => [
            'required'   => 'O campo destinatário é obrigatório.',
            'max_length' => 'O destinatário deve ter no máximo 11 caracteres.',
        ],
        'mensagem' => [
            'required'   => 'O campo mensagem é obrigatório.',
        ],
        'status' => [
            'required' => 'O campo status é obrigatório.',
            'in_list'  => 'O status deve ser 0 (pendente) ou 1 (enviado).',
        ],
        'data_cadastro' => [
            'valid_date' => 'O campo data de cadastro deve ser uma data válida.',
        ],
        'categoria' => [ 
            'required'              => 'O campo categoria é obrigatório.',
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

    public function deleteByMatriculaDatas(array $matriculas, array $datas)
    {
        if (empty($matriculas) || empty($datas)) {
            return false;
        }

        $alunoTelefoneModel = new AlunoTelefoneModel();

        $telefones = $alunoTelefoneModel->select('telefone')
            ->whereIn('aluno_id', $matriculas)
            ->where('status', 1)
            ->findAll();

        $destinatarios = array_column($telefones, 'telefone');

        if (empty($destinatarios)) {
            return false;
        }

        foreach ($destinatarios as $telefone) {
            foreach ($datas as $data) {
                $result = $this->where('destinatario', trim($telefone))
                            ->like('mensagem', trim($data), 'both') 
                            ->delete();
            }
        }
    }
}
