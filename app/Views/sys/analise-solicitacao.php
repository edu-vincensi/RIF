<div class="mb-3"> 
    <h2>Análise das Solicitações</h2>
</div>
    <table border="1" cellpadding="5" cellspacing="0" style="width:100%; text-align:center;">
        <thead>
            <tr>
                <th>Solicitante</th>
                <th>Data da Solicitação</th>
                <th>Data da Retirada</th>
                <th>Link via SEI</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($solicitacoes)): ?>
                <?php foreach ($solicitacoes as $solicitada): ?>
                    <tr>
                        <td><?= esc($solicitada['nome_solicitante'] ?? '—') ?></td>
                        <td><?= date('d/m/Y', strtotime($solicitada['data_solicitada'])) ?></td>
                        <td><?= esc($solicitada['data_refeicao'] ?? '—') ?></td>
                        <td>—</td>
                        <td>
                            <form method="post" action="<?= site_url('sys/analise/atualizar') ?>" style="display:inline;">
                                <input type="hidden" name="id" value="<?= esc($solicitada['id']) ?>">
                                <input type="hidden" name="status" value="1">
                                <button type="submit">Aceitar</button>
                            </form>

                            <form method="post" action="<?= site_url('sys/analise/atualizar') ?>" style="display:inline;">
                                <input type="hidden" name="id" value="<?= esc($solicitada['id']) ?>">
                                <input type="hidden" name="status" value="2">
                                <button type="submit">Recusar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>