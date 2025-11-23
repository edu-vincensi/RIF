<?php 
namespace App\Models;

use CodeIgniter\Model;

class AnaliseDeSolicitacaoModel extends Model
{
    protected $table = 'solicitacao_refeicoes';
    protected $primaryKey = 'id';
    protected $allowedFields = ['status'];

   public function getSolicitacoesPendentes()
    {
    return $this->db->table('solicitacao_refeicoes sr')
    ->select('sr.id, sr.codigo, sr.data_refeicao, sr.data_solicitada, sr.status, sr.id_creat, u.username AS nome_solicitante')
    ->join('users u', 'u.id = sr.id_creat','left')
    ->where('sr.status', 0) // apenas pedentes
    ->orderBy('sr.data_refeicao', 'ASC') // mais antigo vem primeiro
    ->get()
    ->getResultArray();

    }
}