<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OdooService;

class CreateDcCustomFields extends Command
{
    protected $signature   = 'odoo:create-dc-fields';
    protected $description = 'Create custom DC fields on res.partner via Odoo XML-RPC';

    public function __construct(protected OdooService $odoo)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $modelId = $this->getModelId('res.partner');

        $fields = [
            ['name' => 'x_dc_code',      'field_description' => 'DC Code',        'ttype' => 'char'],
            ['name' => 'x_dc_area',      'field_description' => 'Area',            'ttype' => 'char'],
            ['name' => 'x_min_lead_day', 'field_description' => 'Min Lead Day',    'ttype' => 'integer'],
            ['name' => 'x_max_lead_day', 'field_description' => 'Max Lead Day',    'ttype' => 'integer'],
            ['name' => 'x_approved_by',  'field_description' => 'DC Approved By',  'ttype' => 'char'],
            ['name' => 'x_approved_at',  'field_description' => 'DC Approved At',  'ttype' => 'datetime'],
            ['name' => 'x_pulau',        'field_description' => 'Pulau',           'ttype' => 'char'],
            ['name' => 'x_propinsi',     'field_description' => 'Propinsi',        'ttype' => 'char'],
            ['name' => 'x_kecamatan',    'field_description' => 'Kecamatan',       'ttype' => 'char'],
            ['name' => 'x_kelurahan',    'field_description' => 'Kelurahan',       'ttype' => 'char'],
        ];

        foreach ($fields as $field) {
            $existing = $this->odoo->searchRead(
                'ir.model.fields',
                [
                    ['model_id', '=', $modelId],
                    ['name',     '=', $field['name']],
                ],
                ['id', 'name'],
                1,
                0
            );

            if (!empty($existing)) {
                $this->line("  <comment>SKIP</comment>    {$field['name']} (already exists, id: {$existing[0]['id']})");
                continue;
            }

            $id = $this->odoo->create('ir.model.fields', [
                'name'              => $field['name'],
                'field_description' => $field['field_description'],
                'ttype'             => $field['ttype'],
                'model_id'          => $modelId,
                'store'             => true,
                'copied'            => false,
            ]);

            $this->line("  <info>CREATED</info>  {$field['name']} → id: {$id}");
        }

        $this->info('Done.');
    }

    private function getModelId(string $model): int
    {
        $result = $this->odoo->searchRead(
            'ir.model',
            [['model', '=', $model]],
            ['id'],
            1,
            0
        );

        if (empty($result)) {
            throw new \RuntimeException("Model '{$model}' not found in Odoo.");
        }

        return $result[0]['id'];
    }
}