<?php
namespace Drupal\common_utilities\Utilities;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Views;

class commonUtil {
    public static function getProjectListByStage($project_stage) {
        return (Url::fromRoute('projects.list',
          array(
            'project_stage' => urldecode($project_stage),
        ))->toString());
    }

    public static function getKeyFromArray($arr_project_stage, $arr_search) {
        $list_keys = '';
        foreach($arr_search as $val_search) {
            $key = array_search(strtolower($val_search), array_map('strtolower', $arr_project_stage));
            if ($key != '') {
                $list_keys.= $key.",";
            }
        }
        $list_keys = rtrim($list_keys, ",");
        return $list_keys;      
    }
    
    public static function check_role_to_allow($current_roles, $arr_allow_role) {
        foreach ($arr_allow_role as $val_allow_role) {
            if (in_array($val_allow_role, $current_roles)) {
                return true;
            }
        }
        return false;
    }

    public static function secondsToWords($seconds) {
        $ret = "";

        // get the days
        $days = intval(intval($seconds) / (3600*24));
        if($days> 0) {
            //$ret .= "$days days ";
            return "$days days ";
        }

        // get the hours
        $hours = (intval($seconds) / 3600) % 24;
        if($hours > 0) {
            //$ret .= "$hours hours ";
            return  "$hours hours ";
        }

        //get the minutes
        $minutes = (intval($seconds) / 60) % 60;
        if($minutes > 0) {
            //$ret .= "$minutes minutes ";
            return "$minutes minutes ";
        }

        // get the seconds
        $seconds = intval($seconds) % 60;
        if ($seconds > 0) {
            //$ret .= "$seconds seconds";
            return "$seconds seconds";
        }
        //return $ret;
    }

    public static function getTermById($id) {
        if ($id != NULL) {
            $term = Term::load($id);
            return $term->getName();
        }
    }

    public static function getUsersByRole($role = 'authenticated') {
        $userlist = array();
        $ids = \Drupal::entityQuery('user')
        ->condition('status', 1)
        ->condition('roles', $role)
        ->execute();
        $users = User::loadMultiple($ids);
        foreach($users as $user){
          $username = $user->get('name')->value;
          $uid = $user->get('uid')->value;
          $userlist[$uid] = $username;
        }

        // If user not found
        if (is_array($userlist) && count($userlist) <= 0) {
          $userlist = array('0' => 'User not found');
        } else {
          $userlist['0'] = 'Select user';
        }

        ksort($userlist);
        return $userlist;
    }
  

    public static function fn_array_key_first(array $arr) {
        foreach($arr as $key => $value) {
            return $key;
        }
        return NULL;
    }

    public static function getUsernameById($uid) {
      if ($uid > 0) {
        $account = \Drupal\user\Entity\User::load($uid);
        if ($account != NULL)
        return $account->getUsername();
      }
      return false;
    }

    public static function getEmailById($uid) {
      if ($uid > 0) {
        $account = \Drupal\user\Entity\User::load($uid);
        return $account->getEmail();
      }
      return false;
    }

    public static function clean_string($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }

    public static function generate_iwo_code($iwo_id) {
        //$iwo_code = "IWO - ".sprintf('%08d', $iwo_id);
        $iwo_code = "IWO - ".$iwo_id;
        return $iwo_code;
    }

    public static function get_term_list($vid, $type = "name") {
        $arr_term_list = array();

        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', $vid);
        $query->sort('tid');
        $tids = $query->execute();
        $terms = \Drupal\taxonomy\Entity\Term::loadMultiple($tids);

        foreach($terms as $term) {
            $tid = $term->id();
            if ($type == "all") {
                $arr_term_list[$tid]['name'] = $term->get("name")->value;
                if ($vid == "compliances" || $vid == "business_vertical") {
                    $arr_term_list[$tid]['short_name'] = $term->get("field_short_name")->value;
                }
                if ($vid == "compliances") {
                    $arr_term_list[$tid]['mandatory'] = $term->get("field_mandatory")->value;
                    $arr_term_list[$tid]['team'] = $term->get("field_team")->value;
                    $arr_term_list[$tid]['parent'] = $term->parent[0]->target_id;
                }
            } elseif ($type == "color") {
                $arr_term_list[$tid]['name'] = $term->get("name")->value;
                $arr_term_list[$tid]['color'] = $term->get("field_graph_color")->value;
            } else {
                $arr_term_list[$tid] = $term->get("name")->value;
            }
        }
        return $arr_term_list;
    }

    public static function my_generate_hyperlink($text, $path) {
        return Link::fromTextAndUrl(t($text),
            Url::fromUri( $path,
                array('absolute' => TRUE,)))->toString();
    }

    public static function my_generate_url($path) {
        return Url::fromUri('internal:' . $path,
            array('absolute' => TRUE,))->toString();
    }

    public static function my_goto($path) {
        //$path = self::my_generate_url($path);
        $response = new RedirectResponse( $path, 302);
        $response->send();
        return;
    }

    public static function my_goto_error() {
        $path = self::my_generate_url("/error");
        $response = new RedirectResponse( $path, 302);
        $response->send();
        return;
    }

    public static function my_round($number) {
        return round(($number),2,PHP_ROUND_HALF_UP);
    }

    public static function my_number_format($number, $type = 'inr', $decimal = 0) {
        if ($type == 'usd') {
            return '$ ' . number_format($number, $decimal);
        }
        return 'INR ' . number_format($number, $decimal);
    }

    public static function my_round_format($number, $type = 'inr', $decimal = 0) {
        return self::my_number_format(self::my_round($number), $type, $decimal);
    }

    public static function getTagValues($str, $type = '') {
        if ($type == 'gtlt') {
            preg_match("/<(.+?)>(.+?)<[\/](.+?)>/", $str, $matches);
        } else {
            preg_match("/{(.+?)}(.+?){[\/](.+?)}/", $str, $matches);
        }
        return array('tag' => $matches[1], 'content' => $matches[2]);
    }

    public static function clean($string) {
        // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9-$&*!<>|#@;\:,.\/():\'\" ]/', '-', $string); // Removes special chars.
        return trim($string);
    }

    public static function mime_content_type($filename) {
        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}