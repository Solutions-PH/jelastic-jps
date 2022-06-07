<?php
/*
 * This file is main class of the Jelastic API Client PHP.
 * (c) sysastro <sysastro@gmail.com>
 * Fill free for this code to copyright, license and modification.
 * Created base on JElastic API Document : http://apidoc.devapps.jelastic.com/4.7-private/
 * Version : 1.0
 */

class Jelastic
{
    public $apiUrl = null;
    public $apiUsername = null;
    public $apiPassword = null;
    public $signUpAppId = null;
    public $JcaAppId = null;
    public $dashboardAppId = null;
    public $cookiesFile = null;
    public $group = null;
    public $dashboardUrl = null;

    /*
     * In this function we set the credentials for access jelastic api
     * We need main information jelastic api like apiUrl, apiUsername , apiPassword, etc
     */
    public function __construct()
    {
        $this->apiUrl = 'https://app.rag-control.hosteur.com/1.0/';
        $this->apiUsername = 'edaubin@ospharea.com';
        $this->apiPassword = 'Jb220754!!**';
        $this->signUpAppId = 'xxxxxxxxxxxxxxxxxxxxx';
        $this->JcaAppId = 'dashboard';
        $this->dashboardAppId = 'xxxxxxxxxxxxxxxxxxxxx';
        $this->cookiesFile = 'cookies/jelastic.txt';
        $this->group = 'cp';
        $this->dashboardUrl = 'https://xxxxxxxxxxxxxxxxxxxxx/';
    }

    /*
     * Main function for send and request data to jelastic api
     * @param string $restApiPath = required param for additional path url api
     * @param array $method = method for accessing api GET or POST
     * @param array $params = all data params that need to send as post fields
     */
    protected function _getRest($restApiPath, $method, $params = array())
    {
        $queryString = '';
        ksort($params);

        foreach ($params as $key => $value) {
            if ($queryString !== '') {
                $queryString .= '&';
            }
            if (($method == 'PUT') || ($method == 'GET')) {
                $queryString .= $key . '=' . urlencode($value);
            }
        }

        if($method === 'GET') {
            if(!empty($params)) {
                $accessUrl = $this->apiUrl . $restApiPath . '?' . $queryString;
            } else {
                $accessUrl = $this->apiUrl . $restApiPath;
            }
        } else {
            $accessUrl = $this->apiUrl . $restApiPath;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $accessUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 360);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if($method === "POST")
        {
            $postData = [];
            foreach($params as $key=>$value) {
                $postData[] = $key."=".$value;
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, implode("&", $postData));
        } else if($method === "DELETE"){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        } else if($method === "GET"){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        }

        $result = curl_exec($ch);

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = json_decode($result, true);
        curl_close($ch);

        return $result;
    }

    /*
     * Api login for get session
     * @param array $params = all data params that need to send as post fields
     */
    public function login($params = array())
    {
        $path = "users/authentication/rest/signin";
        $result = $this->_getRest($path, 'POST', $params);
        return $result;
    }

    /*
     * Change password user to login jelastic dashboard
     * @param array $params = all data params that need to send as post fields
     */
    public function changePassword($params = array())
    {
        $path = "users/account/rest/changepassword";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }

    /*
     * Check the user with email params is already exist or not
     * @param array $params = all data params that need to send as post fields
     */
    public function checkUser($params = array())
    {
        $path = "users/account/rest/checkuser";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }

    /*
     * Create account wiht login as admin first
     * @param array $params = all data params that need to send as post fields
     */
    public function createAccount($params = array())
    {
        $path = "system/admin/rest/createaccount";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }

    /*
     * Enable account after account created
     * @param array $params = all data params that need to send as post fields
     */
    public function enableAccount($params = array())
    {
        $path = "development/scripting/rest/eval";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }

    /*
     * Get user information detail
     * @param array $params = all data params that need to send as post fields
     */
    public function getUid($params = array())
    {
        $path = "system/admin/rest/getuserinfo";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }
    
    /*
     * Marketplace jps detail
     * @param array $params = all data params that need to send as post fields
     */
    public function marketplaceJpsInstall($params = array())
    {
        $path = "marketplace/jps/rest/install";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }
    
    /*
     * Get repos list
     * @param array $params = all data params that need to send as post fields
     */
    public function getRepos($params = array())
    {
        $path = "environment/deployment/rest/getrepos";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }
    
    /*
     * Add repo
     * @param array $params = all data params that need to send as post fields
     */
    public function addRepo($params = array())
    {
        $path = "environment/deployment/rest/addrepo";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }
    
    /*
     * Deploy app
     * @param array $params = all data params that need to send as post fields
     */
    public function deploy($params = array())
    {
        $path = "environment/deployment/rest/deploy";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }
    
    /*
     * Deploy app
     * @param array $params = all data params that need to send as post fields
     */
    public function execCmd($params = array())
    {
        $path = "environment/control/rest/execcmdbygroup";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }
    
    /*
     * Get envs
     * @param array $params = all data params that need to send as post fields
     */
    public function getEnvs($params = array())
    {
        $path = "environment/control/rest/getenvs";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }
    
    /*
     * Get env info
     * @param array $params = all data params that need to send as post fields
     */
    public function getEnvInfo($params = array())
    {
        $path = "environment/control/rest/getenvinfo";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }
    
    /*
     * Restart node
     * @param array $params = all data params that need to send as post fields
     */
    public function restart($params = array())
    {
        $path = "environment/control/rest/restartservices";
        $result = $this->_getRest($path, 'GET', $params);
        return $result;
    }
        
    
    
    

}