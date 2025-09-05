<?php 

namespace Kang\SystemLogPackage;

use Illuminate\Http\Request;
use Kang\SystemLogPackage\DTO\ApiSystemLogDTO;
use Kang\SystemLogPackage\DTO\WebSystemLogDTO;
use Kang\SystemLogPackage\SystemLogDB;
use Illuminate\Support\Facades\Auth;

class SystemLogHelper
{
    public static function formatSystemLog(Request $data, ?int $user_id, $response): array
    {
        if (self::isApiRequest($data->route()->uri())) {
            $systemLog = self::apiSystemLog($data, $user_id, $response);
        } else {
            $systemLog = self::webSystemLog($data, $user_id, $response);
        }

        SystemLogDB::insert($systemLog);

        return $systemLog;
    }

    private static function apiSystemLog(Request $data, ?int $user_id, $response): array 
    {
        $module = self::getModule($data);

        $systemLogDTO = new ApiSystemLogDTO([
            'type' => 'api',
            'level' => self::getLogLevel($response->getStatusCode()),
            'module' => $module,
            'ref_code' => self::combineRefCode($module),
            'message' => $data->action,
            'user_id' => isset($user_id) ? $user_id : (Auth::check() ? Auth::id() : null),
            'ip_address' => $data->ip(),
            'request_path' => $data->Url(),
            'user_agent' => $data->header('User-Agent'),
            'raw_payload' => self::getRawPayload($data),
        ]);

        return $systemLogDTO->toArray();
    }

    private static function webSystemLog(Request $data, ?int $user_id, $response): array 
    {
        $module = self::getModule($data);
        
        $systemLogDTO = new WebSystemLogDTO([
            'type' => 'web',
            'level' => self::getLogLevel($response->getStatusCode()),
            'module' => $module,
            'ref_code' => self::combineRefCode($module === null ? $data->route()->getAction()['defaults']['module'] : $module),
            'message' => self::combineMessage($data),
            'user_id' => isset($user_id) ? $user_id : (Auth::check() ? Auth::id() : null),
            'ip_address' => $data->ip(),
            'request_path' => $data->url(),
            'user_agent' => $data->header('User-Agent'),
            'raw_payload' => self::getRawPayload($data),
        ]);

        return $systemLogDTO->toArray();
    }

    private static function isApiRequest($uri): bool
    {
        return str_starts_with($uri, 'api');
    }

    private static function getModule($data): ?string
    {
        if(!isset($data->route()->getAction()['defaults'])) {
            throw new \Exception('請在路徑中設置defaults');
        }

        if(!isset($data->route()->getAction()['defaults']['module'])) {
            throw new \Exception('請在defaults內設置module，如：\'defaults\' => [\'module\' => \'XXX\']');
        }

        return $data->module ?: $data->route()->getAction()['defaults']['module'];
    }

    private static function getLogLevel(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            default => 'info'
        };
    }

    private static function combineRefCode($module): string
    {
        return "{$module}-001-001";
    }

    private static function combineMessage($data): string
    {
        $subItem = $data->route()->getAction()['defaults']['sub_item'] ?? null;
        $action = $data->action;

        return "{$action}";
    }

    private static function getRawPayload(Request $data): string
    {
        if ($data instanceof Request) {
            $payload = $data->except(['_token']);

            if (isset($payload['_token'])) {
                $payload['_token'] = '***FILTERED***';
            }
            if (isset($payload['password'])) {
                $payload['password'] = '***FILTERED***';
            }
            if (isset($payload['lineAccessToken'])) {
                $payload['lineAccessToken'] = '***FILTERED***';
            }
        } elseif (is_array($data)) {
            $payload = $data;
            unset($payload['_token']);
            unset($payload['password']);
            unset($payload['lineAccessToken']);
        } else {
            $payload = [];
        }

        return json_encode($payload, JSON_UNESCAPED_UNICODE);
    }
}
