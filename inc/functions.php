<?php 

/**
 * Prevent direct access to a file
 */
function prevent_direct_access() {
    if (defined('APP')) return;
    http_response_code(404);
    exit;
}

/**
 * Prevent direct access to the functions.php file
 */
if (realpath(__FILE__) == realpath($_SERVER['DOCUMENT_ROOT'] . $_SERVER['SCRIPT_FILENAME'])) {
    prevent_direct_access();
}

/**
 * Disable the prevent_direct_access() function
 */
function start() {
    define('APP', true);
}

/**
 * Get configuration values from conf.inc.php
 * 
 * @param array $keys [['name' => 'MS_TENANT_ID', 'default' => 'common'], ['name' => 'MS_CLIENT_ID', 'required' => true], ['name' => 'MS_DOMAIN_HINT', 'required' => false]]
 * @return array ['MS_TENANT_ID' => 'common', '_missing_required' => ['MS_CLIENT_ID'], '_missing_optional' => ['MS_DOMAIN_HINT']]
 */
function get_conf(array $keys): array {
    require_once(dirname(__FILE__) . '/conf.php');
    $conf = ['_missing_required' => [], '_missing_optional' => []];
    foreach ($keys as $key) {
        if (defined($key['name'])) {
            $conf[$key['name']] = constant($key['name']);
        } else if ((isset($key['required']) || !$key['required']) && (isset($key['default']))) {
            $conf[$key['name']] = $key['default'];
        } else {
            $missing = $key['required'] ? '_missing_required' : '_missing_optional';
            array_push($conf[$missing], $key);
        }
    }
    return $conf;
}

/**
 * Get the login URL for Microsoft OAuth
 * @param string $login_hint optional login hint
 * @return array ['data' => 'https://login.microsoftonline.com/...']
 * @return array ['error' => 'Missing required configuration keys in conf.inc.php: MS_CLIENT_ID']
 */
function login_url(string $login_hint = ""): array {
    $conf = get_conf([
        ['name' => 'MS_TENANT_ID', 'default' => 'common'],
        ['name' => 'MS_CLIENT_ID', 'required' => true],
        ['name' => 'MS_REDIRECT_URI', 'required' => true],
        ['name' => 'MS_SCOPES', 'default' => '.default'],
        ['name' => 'MS_DOMAIN_HINT'],
    ]);
    if (!empty($conf['_missing_required'])) return ['error' => 'Missing required configuration keys in conf.inc.php: ' . join(', ', $conf['_missing_required'])];

    $url = 'https://login.microsoftonline.com/' . $conf['MS_TENANT_ID'] . '/oauth2/v2.0/authorize';
    $params = [
        'client_id' => $conf['MS_CLIENT_ID'],
        'response_type' => 'code',
        'redirect_uri' => $conf['MS_REDIRECT_URI'],
        'scope' => join(' ', $conf['MS_SCOPES'])
    ];
    
    if (in_array('MS_DOMAIN_HINT', $conf)) $params['domain_hint'] = $conf['MS_DOMAIN_HINT'];
    if (!empty($login_hint)) $params['login_hint'] = $login_hint;
    
    $urlparams = http_build_query($params);
    
    $url .= '?' . $urlparams;
    
    return ['data' => $url];
}

/**
 * Get the user info from Microsoft Graph API
 * @return array ['data' => ['id' => '...', 'displayName' => '...', ...]]
 * @return array false if no code is provided
 * @return array ['error' => 'Missing code']
 */
function get_user_info(): array|false {
    if (empty($_GET['code'])) return false;
    $conf = get_conf([['name' => 'MS_USER_ATTRIBUTES', 'required' => true]]);

    if (!empty($conf['_missing_required'])) return ['error' => 'Missing required configuration keys in conf.inc.php: ' . join(', ', $conf['_missing_required'])];


    $token = get_access_token($_GET['code']);
    if (isset($token['error'])) return $token;

    $url = 'https://graph.microsoft.com/v1.0/me';
    $url .= '?' . http_build_query(['$select' => join(',', $conf['MS_USER_ATTRIBUTES'])]);

    $request = curl_init($url);
    curl_setopt($request, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token['data']]);
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($request);
    curl_close($request);

    return ['data' => json_decode($response, true)];
}

/**
 * Get the access token from Microsoft OAuth
 * @param string $code the code from the OAuth callback
 * @return array ['data' => ...]
 * @return array ['error' => '...']
 */
function get_access_token(string $code): array {
    if (empty($code)) return ['error' => 'Missing code'];
    
    $conf = get_conf([
        ['name' => 'MS_TENANT_ID', 'default' => 'common'],
        ['name' => 'MS_CLIENT_ID', 'required' => true],
        ['name' => 'MS_CLIENT_SECRET', 'required' => true],
        ['name' => 'MS_REDIRECT_URI', 'required' => true],
        ['name' => 'MS_SCOPES', 'default' => '.default'],
    ]);
    if (!empty($conf['_missing_required'])) return ['error' => 'Missing required configuration keys in conf.inc.php: ' . join(', ', $conf['_missing_required'])];

    $request = curl_init('https://login.microsoftonline.com/' . $conf['MS_TENANT_ID'] . '/oauth2/v2.0/token');
    curl_setopt($request, CURLOPT_POST, 1);
    curl_setopt($request, CURLOPT_POSTFIELDS, http_build_query([
        'client_id' => $conf['MS_CLIENT_ID'],
        'scope' => $conf['MS_SCOPES'],
        'code' => $code,
        'redirect_uri' => $conf['MS_REDIRECT_URI'],
        'grant_type' => 'authorization_code',
        'client_secret' => $conf['MS_CLIENT_SECRET']
    ]));
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($request);
    curl_close($request);

    $response = json_decode($response, true);

    if (isset($response['access_token'])) return ['data' => $response['access_token']];
    if (isset($response['error_description'])) return ['error' => urldecode($response['error_description'])];
    return ['error' => 'Unknown error while getting access token'];
}

/**
 * Convert a variable to html string
 * @param mixed $value the variable to convert
 * @param bool $first if the list is the first in a series
 * @return string the html string
 */
function to_string(mixed $value, $first = true): string {
    if (is_bool($value)) return $value ? 'true' : 'false';

    if (is_string($value)) return $value;

    if (is_array($value)) {
        if ($first) $output = '<ul class="list-disc list-inside">';
        else $output = '<ul class="list-disc list-inside pl-10">';
        if (array_is_list($value)) foreach ($value as $val) {
            if (is_array($val)) $v = '<pre>' . json_encode($val, JSON_PRETTY_PRINT) . '<pre>';
            else $v = to_string($val, true);
            $output .= '<li>' . $v . '</li>';
        } else foreach ($value as $key => $val) {
            if (is_array($val)) $v = '<pre>' . json_encode($val, JSON_PRETTY_PRINT) . '<pre>';
            else $v = to_string($val, true);
            $output .= '<li>' . $key . ': ' . $v . '</li>';
        }
        $output .= '</ul>';
        return $output;
    }

    return print_r($value, true);
}

/**
 * Convert Microsft Graph user data to an html table
 * @param array $data the user data
 * @return string the html table
 */
function data_to_table(array $data): string {
    $output = '
    <table class="w-full text-sm text-left text-gray-400 table-fixed">
        <thead class="text-xs uppercase bg-gray-700 text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3 w-64">Attribute</th>
                <th scope="col" class="px-6 py-3">Value</th>
            </tr>
        </thead>
        <tbody>';
    $end_row = '</td></tr>';
    foreach ($data as $key => $value) {
        if (!empty($value)) {
            $start_row = '<tr class="odd:bg-gray-900 even:bg-gray-800 border-gray-700"><th scope="row" class="px-6 py-4 font-medium whitespace-nowrap text-white">' . $key . '</th><td class="px-6 py-4 break-words">';
            if ($key === "mySite") $output .= $start_row . '<a href="' . $value . '">' . $value . '</a>' . $end_row;
            elseif ($key === "mail") $output .= $start_row . '<a href="mailto:' . $value . '">' . $value . '</a>' . $end_row;
            elseif ($key === "assignedLicenses") {
                $output .= $start_row . '<ul>';
                foreach ($value as $license) {
                    if (empty($license['disabledPlans'])) $output .= '<li>SKU id : ' . $license['skuId'] . '</li>';
                    else {
                        $output .= '<li>SKU id : ' . $license['skuId'] . ', disabled plans:<ul class="list-disc list-inside pl-5">';
                        foreach ($license['disabledPlans'] as $disabled) $output .= '<li>' . $disabled . '</li>';
                        $output .= '</ul></li>';
                    }
                }
                $output .= '</ul>' . $end_row;
            } elseif ($key === "assignedPlans") {
                $output .= $start_row . '<ul>';
                foreach ($value as $plan) {
                    $output .= '<li>Service: ' . $plan['service'] . '</li><ul class="list-disc list-inside pl-5">';
                    $output .= '<li>Service Plan Id: ' . $plan['servicePlanId'] . '</li>';
                    $output .= '<li>Capability Status: ' . $plan['capabilityStatus'] . '</li>';
                    $output .= '<li>Assigned on: ' . $plan['assignedDateTime'] . '</li></ul>';
                }
                $output .= '</ul>' . $end_row;
            } elseif ($key === "licenseAssignmentStates") {
                $output .= $start_row . '<ul>';
                foreach ($value as $state) {
                    $output .= '<li>SKU id: ' . $state['skuId'] . '</li><ul class="list-disc list-inside pl-5">';
                    $output .= '<li>State: ' . $state['state'] . '</li>';
                    if ($state['error'] !== "None") $output .= '<li>Assigned by group: ' . $state['assignedByGroup'] . '</li>';
                    if (!empty($state['assignedByGroup'])) $output .= '<li>Error: ' . $state['error'] . '</li>';
                    $output .= '<li>Last update: ' . $state['lastUpdatedDateTime'] . '</li>';
                    if (!empty($state['disabledPlans'])) {
                        $output .= '<li>Disabled plans:<ul class="list-disc list-inside pl-10">';
                        foreach ($state['disabledPlans'] as $disabled) $output .= '<li>' . $disabled . '</li>';
                        $output .= '</ul></li>';
                    }

                    $output .= '</ul></li>';
                }
                $output .= '</ul>' . $end_row;
            } elseif ($key === "onPremisesExtensionAttributes") {
                $ext = [];
                foreach ($value as $key => $value) {
                    if (!empty($value)) $ext[$key] = $value;
                }
                if (!empty($ext)) $output .= $start_row . to_string($ext) . $end_row;
            } elseif ($key === "passwordProfile") {
                $output .= $start_row . 'Force change password at next sign-in: ' . to_string($value['forceChangePasswordNextSignIn']) . '</br>Force change password at next sign-in with MFA: ' . to_string($value['forceChangePasswordNextSignInWithMfa']) . $end_row;
            } elseif ($key === "provisionedPlans") {
                $output .= $start_row . '<ul>';
                foreach ($value as $plan) {
                    $output .= '<li>Service: ' . $plan['service'] . '</li><ul class="list-disc list-inside pl-5">';
                    $output .= '<li>Capability Status: ' . $plan['capabilityStatus'] . '</li>';
                    $output .= '<li>Provisioning Status: ' . $plan['provisioningStatus'] . '</li></ul>';
                }
                $output .= '</ul>' . $end_row;
            } else $output .= $start_row . to_string($value) . $end_row;
        }
    }
    $output .= '
        </tbody>
    </table>';
    return $output;
}
