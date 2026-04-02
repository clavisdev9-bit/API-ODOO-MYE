<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OdooService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class OdooController extends Controller
{
    public function __construct(protected OdooService $odoo) {}

    // ---------------------------------------------------------------
    // Students  (res.partner or hr.student — sesuaikan model Odoo-mu)
    // ---------------------------------------------------------------

    /**
     * GET /api/odoo/students
     */
    public function students(Request $request): JsonResponse
    {
        try {
            $limit  = (int) $request->get('limit', 50);
            $offset = (int) $request->get('offset', 0);
            $search = $request->get('search', '');

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

            return response()->json([
                'success' => true,
                'total'   => $total,
                'limit'   => $limit,
                'offset'  => $offset,
                'data'    => $records,
            ]);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }

    /**
     * GET /api/odoo/students/{id}
     */
    public function showStudent(int $id): JsonResponse
    {
        try {
            $records = $this->odoo->read(
                'res.partner',
                [$id],
                ['id', 'name', 'email', 'phone', 'mobile', 'street', 'city', 'active', 'comment']
            );

            if (empty($records)) {
                return response()->json(['success' => false, 'message' => 'Student not found'], 404);
            }

            return response()->json(['success' => true, 'data' => $records[0]]);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }

    /**
     * POST /api/odoo/students
     */
    public function createStudent(Request $request): JsonResponse
    {
        $request->validate([
            'name'  => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        try {
            $id = $this->odoo->create('res.partner', [
                'name'         => $request->name,
                'email'        => $request->email,
                'phone'        => $request->phone,
                'mobile'       => $request->mobile,
                'street'       => $request->street,
                'is_company'   => false,
                'customer_rank'=> 1,
            ]);

            return response()->json(['success' => true, 'id' => $id], 201);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }

    /**
     * PUT /api/odoo/students/{id}
     */
    public function updateStudent(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->only(['name', 'email', 'phone', 'mobile', 'street', 'city']);
            $ok   = $this->odoo->write('res.partner', [$id], $data);

            return response()->json(['success' => $ok]);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }

    /**
     * DELETE /api/odoo/students/{id}
     */
    public function deleteStudent(int $id): JsonResponse
    {
        try {
            $ok = $this->odoo->unlink('res.partner', [$id]);
            return response()->json(['success' => $ok]);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }

    // ---------------------------------------------------------------
    // Generic: akses model Odoo apa saja
    // POST /api/odoo/query
    // Body: { model, domain, fields, limit, offset }
    // ---------------------------------------------------------------
    // public function query(Request $request): JsonResponse
    // {
    //     $request->validate(['model' => 'required|string']);

    //     try {
    //         $records = $this->odoo->searchRead(
    //             $request->model,
    //             $request->get('domain', []),
    //             $request->get('fields', []),
    //             (int) $request->get('limit', 100),
    //             (int) $request->get('offset', 0),
    //         );

    //         return response()->json(['success' => true, 'data' => $records]);
    //     } catch (Throwable $e) {
    //         return $this->error($e);
    //     }
    // }

    public function query(Request $request): JsonResponse
{
    $request->validate([
        'model'  => 'required|string',
        'method' => 'nullable|string',
    ]);

    try {
        $model  = $request->model;
        $method = $request->get('method', 'search_read');
        $args   = $request->get('args', []);
        $kwargs = $request->get('kwargs', []);

        $result = $this->odoo->callMethod($model, $method, $args, $kwargs);

        return response()->json(['success' => true, 'data' => $result]);
    } catch (Throwable $e) {
        return $this->error($e);
    }
}

    // ---------------------------------------------------------------
    // Ping / test koneksi
    // GET /api/odoo/ping
    // ---------------------------------------------------------------
    public function ping(): JsonResponse
    {
        try {
            $uid = $this->odoo->authenticate();
            return response()->json(['success' => true, 'uid' => $uid, 'message' => 'Connected to Odoo']);
        } catch (Throwable $e) {
            return $this->error($e);
        }
    }

    // ---------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------
    private function error(Throwable $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}
