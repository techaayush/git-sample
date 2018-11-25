



<?php

defined('BASEPATH') OR exit('No direct script access allowed');

APPPATH . '\vendor\autoload.php';

class Drive extends CI_Controller {

	   public function __construct()
     {
        parent::__construct();
     }

        public function index()
        {
              $client = new Google_Client();
          		$client->setAuthConfigFile('client_secret.json');
          		$client->setRedirectUri('http://localhost/ci');
          		$client->addScope(array
              (Google_Service_Drive::DRIVE,
              Google_Service_Drive::DRIVE_FILE));

              // Add Access Token to Session
              if (isset($_GET['code'])){
                $client->authenticate($_GET['code']);
                $_SESSION['access_token'] = $client->getAccessToken();
              }
              // Set Access Token to make Request
              if (isset($_GET['token'])){
                  $client->setAccessToken($_GET['token']);
              }
              // if ($client->getAccessToken())
              // {
              //     //For logged in user, get details from google using acces
              //     $user=User::find(1);
              //     $user->access_token=json_encode($request->session()->get('token'));
              //     $user->save();
              //     dd("Successfully authenticated");
              // }
              else
              {
                  //For Guest user, get google login url
                  $auth_url = $client->createAuthUrl();
                  header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
              }

          		  $redirect_uri = 'http://localhost/ci/drive/uploadFileUsingAccessToken';
          		  header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }


        public function uploadFileUsingAccessToken(){

          $client = new Google_Client();
          $client->setAuthConfig('client_secret.json');
          $client->addScope(array
          (Google_Service_Drive::DRIVE,
          Google_Service_Drive::DRIVE_FILE));

          if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
            $client->setAccessToken($_SESSION['access_token']);
            $drive = new Google_Service_Drive($client);
            $files = $drive->files->listFiles(array())->getItems();
            echo json_encode($files);
          } else {
            $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/oauth2callback.php';
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
          }





            $service = new \Google_Service_Drive($this->gClient);
            $user=User::find(1);
            $this->gClient->setAccessToken(json_decode($user->access_token,true));
            if ($this->gClient->isAccessTokenExpired()) {

                // save refresh token to some variable
                $refreshTokenSaved = $this->gClient->getRefreshToken();
                // update access token
                $this->gClient->fetchAccessTokenWithRefreshToken($refreshTokenSaved);
                // // pass access token to some variable
                $updatedAccessToken = $this->gClient->getAccessToken();
                // // append refresh token
                $updatedAccessToken['refresh_token'] = $refreshTokenSaved;
                //Set the new acces token
                $this->gClient->setAccessToken($updatedAccessToken);

                $user->access_token=$updatedAccessToken;
                $user->save();
            }

           $fileMetadata = new \Google_Service_Drive_DriveFile(array(
                'name' => 'ExpertPHP',
                'mimeType' => 'application/vnd.google-apps.folder'));
            $folder = $service->files->create($fileMetadata, array(
                'fields' => 'id'));
            printf("Folder ID: %s\n", $folder->id);


            $file = new \Google_Service_Drive_DriveFile(array(
                            'name' => 'cdrfile.jpg',
                            'parents' => array($folder->id)
                        ));
            $result = $service->files->create($file, array(
              'data' => file_get_contents(public_path('images/myimage.jpg')),
              'mimeType' => 'application/octet-stream',
              'uploadType' => 'media'
            ));
            // get url of uploaded file
            $url='https://drive.google.com/open?id='.$result->id;
            dd($result);

        }
      }
