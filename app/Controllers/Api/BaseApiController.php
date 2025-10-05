<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Shield\Entities\User;

class BaseApiController extends ResourceController
{
    use ResponseTrait;

    protected $format = 'json';
    protected $user;
    protected $allowedFilters = [];
    protected $allowedSorts = [];
    protected $defaultLimit = 50;
    protected $maxLimit = 1000;

    public function __construct()
    {
        // No llamar parent::__construct() aquí
    }

    /**
     * Ejecutar antes de cada método del controlador
     */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Verificar autenticación del usuario
     */
    protected function authenticate(): bool
    {
        $auth = service('auth');
        
        // Verificar si el usuario está autenticado
        if (!$auth->loggedIn()) {
            return false;
        }

        $this->user = $auth->user();
        
        // Verificar permisos específicos de API
        if (!$this->user->can('api.access')) {
            return false;
        }

        return true;
    }

    /**
     * Respuesta estandarizada de éxito
     */
    protected function respondSuccess($data = null, string $message = 'Success', int $code = 200)
    {
        return $this->respond([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'meta' => $this->generateMeta($data)
        ], $code);
    }

    /**
     * Respuesta estandarizada de error
     */
    protected function respondError(string $message = 'Error', int $code = 400, $errors = null)
    {
        return $this->respond([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
            'data' => null
        ], $code);
    }

    /**
     * Respuesta para recursos no encontrados
     */
    protected function respondNotFound(string $message = 'Resource not found')
    {
        return $this->respondError($message, 404);
    }

    /**
     * Respuesta para acceso prohibido
     */
    protected function respondForbidden(string $message = 'Access forbidden')
    {
        return $this->respondError($message, 403);
    }

    /**
     * Procesar parámetros de consulta estándar
     */
    protected function processQueryParams(): array
    {
        return [
            'page' => max(1, (int) service('request')->getGet('page') ?? 1),
            'limit' => min($this->maxLimit, max(1, (int) service('request')->getGet('limit') ?? $this->defaultLimit)),
            'sort' => $this->processSortParam(service('request')->getGet('sort')),
            'filters' => $this->processFilters(service('request')->getGet() ?? []),
            'fields' => $this->processFieldsParam(service('request')->getGet('fields')),
        ];
    }

    /**
     * Procesar parámetro de ordenamiento
     */
    protected function processSortParam(?string $sort): array
    {
        if (!$sort) {
            return [];
        }

        $sorts = [];
        $sortFields = explode(',', $sort);

        foreach ($sortFields as $field) {
            $field = trim($field);
            $direction = 'ASC';

            if (str_starts_with($field, '-')) {
                $direction = 'DESC';
                $field = substr($field, 1);
            }

            // Validar que el campo esté permitido
            if (in_array($field, $this->allowedSorts)) {
                $sorts[] = [$field, $direction];
            }
        }

        return $sorts;
    }

    /**
     * Procesar filtros de consulta
     */
    protected function processFilters(array $params): array
    {
        $filters = [];

        foreach ($this->allowedFilters as $filter) {
            if (isset($params[$filter]) && $params[$filter] !== '') {
                $filters[$filter] = $params[$filter];
            }
        }

        return $filters;
    }

    /**
     * Procesar campos específicos a retornar
     */
    protected function processFieldsParam(?string $fields): array
    {
        if (!$fields) {
            return [];
        }

        return array_map('trim', explode(',', $fields));
    }

    /**
     * Generar metadatos para la respuesta
     */
    protected function generateMeta($data): array
    {
        $meta = [
            'timestamp' => date('c'),
            'user_id' => $this->user->id ?? null,
        ];

        // Si es un array de datos, agregar información de paginación
        if (is_array($data) && isset($data['items'])) {
            $meta['pagination'] = [
                'current_page' => $data['current_page'] ?? 1,
                'per_page' => $data['per_page'] ?? $this->defaultLimit,
                'total' => $data['total'] ?? count($data['items'] ?? []),
                'total_pages' => $data['total_pages'] ?? 1,
            ];
        }

        return $meta;
    }

    /**
     * Validar parámetros de fecha
     */
    protected function validateDateRange(?string $dateFrom, ?string $dateTo): array
    {
        $errors = [];

        if ($dateFrom && !$this->isValidDate($dateFrom)) {
            $errors['date_from'] = 'Invalid date format. Use YYYY-MM-DD';
        }

        if ($dateTo && !$this->isValidDate($dateTo)) {
            $errors['date_to'] = 'Invalid date format. Use YYYY-MM-DD';
        }

        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            $errors['date_range'] = 'date_from cannot be later than date_to';
        }

        return $errors;
    }

    /**
     * Validar formato de fecha
     */
    protected function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Log de actividad de API
     */
    protected function logApiActivity(string $action, array $params = [], ?string $result = null): void
    {
        $logData = [
            'user_id' => $this->user->id ?? null,
            'action' => $action,
            'params' => json_encode($params),
            'result' => $result,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getHeaderLine('User-Agent'),
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        // En un entorno real, esto se guardaría en una tabla de logs
        log_message('info', 'API Activity: ' . json_encode($logData));
    }

    /**
     * Aplicar rate limiting (simulado)
     */
    protected function checkRateLimit(): bool
    {
        // En un entorno real, implementaríamos rate limiting real
        // Por ahora, simplemente logueamos el acceso
        $this->logApiActivity('api_access', [
            'endpoint' => $this->request->getUri()->getPath(),
            'method' => $this->request->getMethod()
        ]);

        return true;
    }

    /**
     * Obtener parámetros de agregación temporal
     */
    protected function getTimeAggregation(): string
    {
        $aggregation = service('request')->getGet('time_aggregation') ?? 'daily';
        
        $validAggregations = ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'];
        
        if (!in_array($aggregation, $validAggregations)) {
            $aggregation = 'daily';
        }

        return $aggregation;
    }

    /**
     * Obtener nivel de drill (para drill-down/up)
     */
    protected function getDrillLevel(): string
    {
        $level = service('request')->getGet('drill_level') ?? 'summary';
        
        $validLevels = ['summary', 'category', 'product', 'detail'];
        
        if (!in_array($level, $validLevels)) {
            $level = 'summary';
        }

        return $level;
    }

    /**
     * Verificar si el usuario está autenticado
     */
    protected function isAuthenticated(): bool
    {
        return auth()->loggedIn();
    }

    /**
     * Respuesta de éxito estándar
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): object
    {
        return $this->response->setStatusCode($code)->setJSON([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Respuesta de error estándar
     */
    protected function errorResponse(string $message = 'Error occurred', int $code = 400, $errors = null): object
    {
        return $this->response->setStatusCode($code)->setJSON([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Respuesta de no autorizado
     */
    protected function unauthorizedResponse(string $message = 'Unauthorized access'): object
    {
        return $this->errorResponse($message, 401);
    }

    /**
     * Respuesta de recurso no encontrado
     */
    protected function notFoundResponse(string $message = 'Resource not found'): object
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * Respuesta de validación fallida
     */
    protected function validationResponse(array $errors, string $message = 'Validation failed'): object
    {
        return $this->errorResponse($message, 422, $errors);
    }
}