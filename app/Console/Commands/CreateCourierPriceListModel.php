<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\OdooService;

class CreateCourierPriceListModel extends Command
{
    protected $signature   = 'odoo:create-courier-model';
    protected $description = 'Create custom Courier Price List model on Odoo via XML-RPC';

    public function __construct(protected OdooService $odoo)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        // Step 1: cek atau buat model
        $this->info('Step 1: Checking model x_courier.price.list...');

        $existingModel = $this->odoo->searchRead(
            'ir.model',
            [['model', '=', 'x_courier.price.list']],
            ['id', 'model'],
            1, 0
        );

        if (!empty($existingModel)) {
            $modelId = $existingModel[0]['id'];
            $this->line("  <comment>SKIP</comment>  model already exists (id: {$modelId})");
        } else {
            $modelId = $this->odoo->create('ir.model', [
                'name'      => 'Courier Price List',
                'model'     => 'x_courier.price.list',
                'state'     => 'manual',
                'transient' => false,
            ]);
            $this->line("  <info>CREATED</info> model x_courier.price.list → id: {$modelId}");
        }

        // Step 2: buat fields
        $this->info('Step 2: Creating fields...');

        $fields = [
            ['name' => 'x_name',                 'field_description' => 'Name',                  'ttype' => 'char',      'required' => true],
            ['name' => 'x_customer_id',           'field_description' => 'Customer',              'ttype' => 'many2one',  'relation' => 'res.partner'],
            ['name' => 'x_dc_id',                 'field_description' => 'DC',                    'ttype' => 'many2one',  'relation' => 'res.partner'],
            ['name' => 'x_freight_type',          'field_description' => 'Freight Type',          'ttype' => 'selection',
             'selection' => "[('LAND', 'Land'), ('SEA', 'Sea'), ('AIR', 'Air')]"],
            ['name' => 'x_vendor',                'field_description' => 'Vendor',                'ttype' => 'char'],
            ['name' => 'x_service',               'field_description' => 'Service',               'ttype' => 'char'],
            ['name' => 'x_city_code',             'field_description' => 'City Code',             'ttype' => 'char'],
            ['name' => 'x_base_vendor',           'field_description' => 'Base Vendor',           'ttype' => 'char'],
            ['name' => 'x_prev_effective_code',   'field_description' => 'Prev Effective Code',   'ttype' => 'char'],
            ['name' => 'x_prev_leadtime',         'field_description' => 'Prev Leadtime',         'ttype' => 'float'],
            ['name' => 'x_prev_min_kgs',          'field_description' => 'Prev Min Kgs',          'ttype' => 'float'],
            ['name' => 'x_prev_price',            'field_description' => 'Prev Price',            'ttype' => 'float'],
            ['name' => 'x_latest_effective_code', 'field_description' => 'Latest Effective Code', 'ttype' => 'char'],
            ['name' => 'x_latest_leadtime',       'field_description' => 'Latest Leadtime',       'ttype' => 'float'],
            ['name' => 'x_latest_min_kgs',        'field_description' => 'Latest Min Kgs',        'ttype' => 'float'],
            ['name' => 'x_latest_price',          'field_description' => 'Latest Price',          'ttype' => 'float'],
            ['name' => 'x_latest_doc_leadtime',   'field_description' => 'Latest Doc Leadtime',   'ttype' => 'float'],
            ['name' => 'x_latest_doc_price',      'field_description' => 'Latest Doc Price',      'ttype' => 'float'],
            ['name' => 'x_lowest_min_kgs',        'field_description' => 'Lowest Min Kgs',        'ttype' => 'float'],
            ['name' => 'x_lowest_price',          'field_description' => 'Lowest Price',          'ttype' => 'float'],
            ['name' => 'x_diff_price',            'field_description' => 'Diff Price',            'ttype' => 'float'],
        ];

        foreach ($fields as $field) {
            $existing = $this->odoo->searchRead(
                'ir.model.fields',
                [
                    ['model_id', '=', $modelId],
                    ['name',     '=', $field['name']],
                ],
                ['id', 'name'],
                1, 0
            );

            if (!empty($existing)) {
                $this->line("  <comment>SKIP</comment>    {$field['name']} (already exists, id: {$existing[0]['id']})");
                continue;
            }

            $payload = [
                'name'              => $field['name'],
                'field_description' => $field['field_description'],
                'ttype'             => $field['ttype'],
                'model_id'          => $modelId,
                'store'             => true,
                'copied'            => false,
            ];

            if (isset($field['relation'])) {
                $payload['relation'] = $field['relation'];
            }

            if (isset($field['selection'])) {
                $payload['selection'] = $field['selection'];
            }

            if (isset($field['required'])) {
                $payload['required'] = $field['required'];
            }

            $id = $this->odoo->create('ir.model.fields', $payload);
            $this->line("  <info>CREATED</info>  {$field['name']} → id: {$id}");
        }


        // Step 3: buat access rights
$this->info('Step 3: Creating access rights...');

$existingAccess = $this->odoo->searchRead(
    'ir.model.access',
    [['model_id', '=', $modelId]],
    ['id', 'name'],
    1, 0
);

if (!empty($existingAccess)) {
    $this->line("  <comment>SKIP</comment>  access rights already exists (id: {$existingAccess[0]['id']})");
} else {
    // ambil group id untuk base.group_user (Internal User)
    $group = $this->odoo->searchRead(
        'res.groups',
        [['full_name', '=', 'Internal User']],
        ['id', 'full_name'],
        1, 0
    );

    // fallback cari dengan cara lain kalau tidak ketemu
    if (empty($group)) {
        $group = $this->odoo->searchRead(
            'res.groups',
            [['name', '=', 'User']],
            ['id', 'name'],
            5, 0
        );
    }

    $groupId = !empty($group) ? $group[0]['id'] : false;

    $accessId = $this->odoo->create('ir.model.access', [
        'name'         => 'access_x_courier_price_list',
        'model_id'     => $modelId,
        'group_id'     => $groupId,
        'perm_read'    => true,
        'perm_write'   => true,
        'perm_create'  => true,
        'perm_unlink'  => false,
    ]);

    $this->line("  <info>CREATED</info>  access rights → id: {$accessId}");
}

        $this->info('Done.');
    }
}