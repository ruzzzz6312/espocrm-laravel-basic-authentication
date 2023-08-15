//You Laravel espocrm class goes here

###################################################################################################
#
#
#
# BASIC AUTH FUNCTIONS (DUE TO API USER LIMITATIONS)                                              #
#
#
#
###################################################################################################

private function getAuthorizationHeader()
{
$username = config('espocrm.basic_user');
$password = config('espocrm.basic_password');
return 'Basic ' . base64_encode($username . ':' . $password);
}
private function sendBasicAuthorizationRequest($method, $endpoint, $data = [], $useCache = false)
{
Log::info('Basic Auth API Request: ' . $method . ' ' . $endpoint . ' ' . json_encode($data) . ' cache: ' . $useCache);
$url = $this->baseUrl .'/api/v1/'.$endpoint;

$defaultHeaders = [
'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/116.0',
'Accept' => 'application/json, text/javascript, */*; q=0.01',
'Accept-Language' => 'en-US,en;q=0.5',
'Accept-Encoding' => 'gzip, deflate, br',
'Content-Type' => 'application/json',
'Authorization' => $this->getAuthorizationHeader()
];

try {
$cacheKey = md5($method . $endpoint . serialize($data));

if($useCache && Cache::has($cacheKey)){
Log::info('Returning cached result for Basic Auth request');
return Cache::get($cacheKey);
}

$response = Http::withHeaders($defaultHeaders)->send($method, $url, ['json' => $data]);

Log::info("Basic Auth API Response: ".json_encode($response->json()));

if ($response->successful()) {
$responseData = $response->json();

if($useCache) {
Log::info('Caching result for Basic Auth request');
Cache::put($cacheKey, $responseData, now()->addMinutes(10)); // Cache it for 10 minutes
}

return $responseData;
} else {
throw new \Exception('Unexpected HTTP status: ' . $response->status());
}
} catch (\Exception $e) {
Log::error('Error in request to ' . $endpoint . ': ' . $e->getMessage());
return null;
}
}


public function addUserToTeam($teamId, array $userIds, $cache = false)
{
$endpoint = 'Team/' . $teamId . '/users';
return $this->sendBasicAuthorizationRequest('POST', $endpoint, $userIds, $cache);
}
public function deleteUsersFromTeam($teamId, $userIds, $cache = false)
{
$endpoint = 'Team/' . $teamId . '/users';
return $this->sendBasicAuthorizationRequest('DELETE', $endpoint, $userIds, $cache);
}