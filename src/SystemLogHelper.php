<?php 

namespace Kang\SystemLogPackage;

use Illuminate\Http\Request;
use Kang\SystemLogPackage\DTO\ApiSystemLogDTO;
use Kang\SystemLogPackage\DTO\WebSystemLogDTO;
use Kang\SystemLogPackage\DTO\ScheduleSystemLogDTO;
use Illuminate\Support\Facades\Auth;

class SystemLogHelper
{
    public static function formatSystemLog(Request $data, ?int $user_id, $response, ?bool $isSchedule): array
    {
        if (self::isApiRequest($data->route()->uri())) {
            $systemLog = self::apiSystemLog($data, $user_id, $response);
        } else {
            $systemLog = self::webSystemLog($data, $user_id, $response);
        }

        return $systemLog;
    }

    private static function apiSystemLog(Request $data, ?int $user_id, $response): array 
    {
        $systemLogDTO = new ApiSystemLogDTO([
            'type' => 'api',
            'level' => self::getLogLevel($response->getStatusCode()),
            'module' => $data->route()->getAction()['defaults']['module'],
            'ref_code' => self::combineRefCode($data),
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
        $module = null;
        
        if ($data->route()->getAction()['prefix'] === '') {
            $module =  $data->module;      
        }

        $systemLogDTO = new WebSystemLogDTO([
            'type' => 'web',
            'level' => self::getLogLevel($response->getStatusCode()),
            'module' => $module === null ? $data->route()->getAction()['defaults']['module'] : $module,
            'ref_code' => self::combineRefCode($data),
            'message' => self::combineMessage($data, $module),
            'user_id' => isset($user_id) ? $user_id : (Auth::check() ? Auth::id() : null),
            'ip_address' => $data->ip(),
            'request_path' => $data->url(),
            'user_agent' => $data->header('User-Agent'),
            'raw_payload' => self::getRawPayload($data),
        ]);

        return $systemLogDTO->toArray();
    }

    public static function scheduleSystemLog(Request $data, $module, $ref_code, $response): array
    {
        $scheduleSystemLog = new ScheduleSystemLogDTO([
            'type' => 'schedule',
            'level' => self::getLogLevel($response['status']),
            'module' => $module,
            'ref_code' => $ref_code,
            'message' => $response['message'],
            'user_id' => null,
            'ip_address' => $data->ip(),
            'request_path' => $data->url(),
            'user_agent' => $data->header('User-Agent'),
            'raw_payload' => self::getRawPayload($data),
        ]);

        return $scheduleSystemLog->toArray();
    }

    private static function isApiRequest($uri): bool
    {
        return str_starts_with($uri, 'api');
    }

    private static function getLogLevel(int $statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            default => 'info'
        };
    }

    private static function combineRefCode($data): string
    {
        $module = $data->route()->getAction()['defaults']['module'];

        return "{$module}-001-001";
    }

    private static function combineMessage($data, $module): string
    {
        $subItem = $data->route()->getAction()['defaults']['sub_item'] ?? null;
        $action = $data->action;

        return "{$action}{$module}{$subItem}";
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
