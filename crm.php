<?php
header("Content-Type: application/json");

function write_log($msg)
{
    file_put_contents("/var/tmp/crm_log.txt", "[" . date("Y-m-d H:i:s") . "] $msg\n", FILE_APPEND);
}

$data = json_decode(file_get_contents("php://input"), true);

// BASIC FIELDS
$name = trim($data['name'] ?? '');
$mobile = trim($data['contact'] ?? '');
$email = trim($data['email'] ?? 'noemail@greenwich.com');

$occupation = trim($data['occupation'] ?? '');
$company = trim($data['company'] ?? '');
$address = trim($data['address'] ?? '');

$sizes = isset($data['sizes']) ? implode(", ", $data['sizes']) : '';
$min_price = trim($data['min_price'] ?? '');
$max_price = trim($data['max_price'] ?? '');

$source_list = trim($data['source_list'] ?? '');
$remarks = trim($data['remarks'] ?? '');

// REQUIRED VALIDATION
if (!$name || !$mobile) {
    write_log("Error: Missing name or mobile");
    echo json_encode(["success" => false, "error" => "Missing name or mobile"]);
    exit;
}

if (!$min_price) {
    write_log("Error: Missing min_price");
    echo json_encode(["success" => false, "error" => "Minimum price required"]);
    exit;
}


// ONLY DIGITS
$mobile = preg_replace('/\D/', '', $mobile); // remove non-digits

// if user entered 10 digits, add 91 automatically
if (strlen($mobile) == 10) {
    $mobile = "91" . $mobile;
}

// if user entered 12 digits and already starts with 91, keep as is
if (strlen($mobile) == 12 && substr($mobile, 0, 2) == "91") {
    // do nothing
}

// invalid length
if (strlen($mobile) < 10) {
    write_log("Error: Invalid mobile number format");
    echo json_encode(["success" => false, "error" => "Invalid mobile number"]);
    exit;
}

write_log("Received Request: " . json_encode($data));

// CRM API URL
$api_url = "https://pranavagroup.tranquilcrmone.in/v2/createlead";

// CRM PARAMS (POST body)
$params = [
    "api_key" => "TRNQUILCRMpranavagroup",
    "country_code" => "91",
    "mobile_number" => $mobile,
    "project_id" => "7",
    "source_type" => "17",

    "customer_name" => $name,
    "email" => $email,
    "sub_source" => $source_list,
    "remark" => $remarks,

    "budget" => $min_price . " - " . $max_price,
    "spi" => $sizes,
    "location" => $address,

    "campaign_name" => $occupation,
    "adgroup_name" => $company,

    "requirment_type" => "",
    "property_type" => "",
    "configuration" => "",
    "ad_name" => "",
    "activity_date" => "",
    "activity_time" => "",
    "activity_id" => ""
];

// Build query string exactly as CRM expects
$query = http_build_query($params);

$final_url = $api_url . "?" . $query;

write_log("FINAL CRM URL: " . $final_url);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $final_url);
curl_setopt($ch, CURLOPT_POST, true); // POST allowed, but API uses query params
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

write_log("HTTP CODE: $http_code");
write_log("CRM RESPONSE: $response");

echo json_encode([
    "success" => $http_code == 200,
    "http_code" => $http_code,
    "crm_response" => json_decode($response, true),
    "raw_response" => $response
]);
