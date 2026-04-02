<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentValidationIndex;
use App\Http\Requests\StudentRequest;
use App\Http\Resources\StudentsResources;
use App\Http\Resources\StudentsResourcesCollection;
use App\Services\OdooService;
use App\Helpers\ApiResponse;

class StudentController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    // GET /api/odoo/students
    public function students(StudentValidationIndex $request)
    {
        $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $limit  = is_numeric($validated['limit']  ?? null) ? (int) $validated['limit']  : 10;
        $offset = is_numeric($validated['offset'] ?? null) ? (int) $validated['offset'] : 0;

        $domain = [['is_company', '=', false]];
        if ($search) {
            $domain[] = ['name', 'ilike', $search];
        }

        $total   = $this->odoo->searchCount('res.partner', $domain);
        $records = $this->odoo->searchRead(
            'res.partner',
            $domain,
            ['id', 'name', 'email', 'phone', 'mobile', 'street', 'city', 'active'],
            $limit,
            $offset
        );

        $message = empty($records) ? "Data yang Anda cari tidak ditemukan" : "Success";

        return ApiResponse::paginate(
            new StudentsResourcesCollection($records, $total, $limit, $offset),
            $message
        );
    }

    // GET /api/odoo/students/{id}
    public function showStudent(int $id)
    {
        $records = $this->odoo->read(
            'res.partner',
            [$id],
            ['id', 'name', 'email', 'phone', 'mobile', 'street', 'city', 'active']
        );

        if (empty($records)) {
            return ApiResponse::error('Student not found', [
                'id' => ['Data with that ID is not available']
            ], 404);
        }

        return ApiResponse::success(
            new StudentsResources($records[0]),
            'Success, take the detailed Student',
            200
        );
    }

    // POST /api/odoo/students
    public function storeStudent(StudentRequest $request)
    {
        $data = $request->validated();

        try {
            $id = $this->odoo->create('res.partner', [
                'name'          => $data['name'],
                'email'         => $data['email']  ?? null,
                'phone'         => $data['phone']  ?? null,
                'mobile'        => $data['mobile'] ?? null,
                'street'        => $data['street'] ?? null,
                'city'          => $data['city']   ?? null,
                'is_company'    => false,
                'customer_rank' => 1,
            ]);

            $records = $this->odoo->read(
                'res.partner',
                [$id],
                ['id', 'name', 'email', 'phone', 'mobile', 'street', 'city', 'active']
            );

            return ApiResponse::success(
                new StudentsResources($records[0]),
                'Success Create New Student',
                201
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to create student', [
                'exception' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // PUT /api/odoo/students/{id}
    public function updateStudent(StudentRequest $request, int $id)
    {
        $data = $request->validated();

        $existing = $this->odoo->read(
            'res.partner',
            [$id],
            ['id', 'name']
        );

        if (empty($existing)) {
            return ApiResponse::error('Student with that ID was not found.', [
                'id' => ['Data not available.']
            ], 404);
        }

        try {
            $this->odoo->write('res.partner', [$id], $data);

            $updated = $this->odoo->read(
                'res.partner',
                [$id],
                ['id', 'name', 'email', 'phone', 'mobile', 'street', 'city', 'active']
            );

            return ApiResponse::success(
                new StudentsResources($updated[0]),
                'Success Update Student',
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update student', [
                'exception' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    // DELETE /api/odoo/students/{id}
    public function destroyStudent(int $id)
    {
        $existing = $this->odoo->read(
            'res.partner',
            [$id],
            ['id', 'name', 'email', 'phone', 'mobile', 'street', 'city', 'active']
        );

        if (empty($existing)) {
            return ApiResponse::error('Student with that ID was not found.', [
                'id' => ['Data not available.']
            ], 404);
        }

        try {
            $this->odoo->unlink('res.partner', [$id]);

            return ApiResponse::success(
                new StudentsResources($existing[0]),
                'Success Delete Student',
                200
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to delete student', [
                'exception' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}