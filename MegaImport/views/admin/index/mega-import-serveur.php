<?php
    function openZip($tmppath){//open zip
        //echo "ouverture ... ";
        $zip = new ZipArchive;
        $res = $zip->open($tmppath);
        if ($res === TRUE) {
            $zip->extractTo('/sites/cenhtor/tmp/tempo');
            $zip->close();
            //echo "OK <br>";
        } else {
            //echo 'échec, code:'.$res;
        }
    }

    function dbRetrive($base, $info){
        $db = get_db();
        $sql = 'SELECT * FROM `omeka_mega_imports` WHERE `store` = "'.$base.'"';
        $result = $db->query($sql);
        foreach ($result->fetchAll() as $x) {
            if(array_keys($x, $info)){
                return $x["value"];
            }
        }
    }

    function clear($listfiles) {
        foreach ($listfiles as $filename) {
            unlink('/sites/cenhtor/tmp/tempo/'.$filename);
        }
    }

    function getCollection($projet) {
        $db = get_db();
        $sql = 'SELECT `text` FROM `omeka_element_texts` WHERE `id` = (SELECT `id` FROM `omeka_element_texts` WHERE `text` = "'.$projet.'")+1';
        $result = $db->query($sql);
        return array("name" => $projet, "id" => $result->fetchAll()['0']['text']);
    }

    function getItem($item) {
        $db = get_db();
        $sql = 'SELECT `text` FROM `omeka_element_texts` WHERE `id` = (SELECT `id` FROM `omeka_element_texts` WHERE `text` = "'.$item.'")-5';
        $result = $db->query($sql);
        return array("name" => $item, "id" => $result->fetchAll()['0']['text']);
    }

    function getDataFormat($name, $listefile) {
        foreach ($listefile as $file) {
            if ($name == explode(".", $file)[0] and explode(".", $file)[1] != ".csv") {
                return $file;
            }
        }
    }

    function verifFiles($list_files){//check files validity
        $list_nope = array("php", "js", "html", "css");
        $verif = array();
        foreach ($list_files as $file) {
            $check = explode(".", $file);
            if (!in_array($check[1], $list_nope)) {
                $verif[$file] = $check;
            }
            else {
                //echo "extension de fichier interdite : ".$verif[$file][0].$verif[$file][1]."<br>";
            }
        }
        return $verif;
    }

    function workMeta($verif, $list_files){//fill the metadata
        foreach ($verif as $duo) {
            if($duo[1] == "csv") {
                $file = fopen("/sites/cenhtor/tmp/tempo/".$duo[0].".csv","r");
                $firstline = fgetcsv($file);
                while(! feof($file)) {
                    $secondline = fgetcsv($file);
                    if ($secondline[0] != "") {
                        $fp = fopen('/sites/cenhtor/tmp/tempo/'.$secondline[0].'.csv', 'w');
                        for ($i = 0; $i < count($firstline); $i++) {
                            if ($firstline[$i] == "projet") {
                                $collection = getCollection($secondline[$i]);
                                $line = "nkl:inCollection".",".$collection["id"];
                                fputcsv($fp, explode(',',$line));
                            } else {
                                if ($firstline[$i] == "created"){
                                    $line = "date,".$secondline[$i];
                                    fputcsv($fp, explode(',',$line, 2));
                                    $line = $firstline[$i].",".$secondline[$i];
                                    fputcsv($fp, explode(',',$line, 2));
                                }
                                elseif ($firstline[$i] == "creator"){
                                    $creas = preg_split("/,| et /", $secondline[$i]);
                                    foreach ($creas as $crea) {
                                        if ($crea[0] == " "){
                                            $crea = substr($crea, 1);
                                        }
                                        if ($crea[strlen($crea)-1] == " "){
                                            $crea = substr($crea, 0, strlen($crea)-1);
                                        }
                                        $line = $firstline[$i].",".$crea;
                                        fputcsv($fp, explode(',',$line, 2));
                                    }
                                }
                                elseif ($firstline[$i] == "subject"){
                                    $subjs = preg_split("/,/", $secondline[$i]);
                                    foreach ($subjs as $subj) {
                                        if ($subj[0] == " "){
                                            $subj = substr($subj, 1);
                                        }
                                        if ($subj[strlen($subj)-1] == " "){
                                            $subj = substr($subj, 0, strlen($subj)-1);
                                        }
                                        $line = $firstline[$i].",".$subj;
                                        fputcsv($fp, explode(',',$line, 2));
                                    }
                                }
                                else{
                                    $line = $firstline[$i].",".$secondline[$i];
                                    fputcsv($fp, explode(',',$line, 2));
                                }
                            }
                        }
                        $line = "nkl:dataFormat".",".explode(".", getDataFormat($secondline[0], $list_files))[1];
                        fputcsv($fp, explode(',',$line));
                        fclose($fp);
                    }
                }
                fclose($file);
                unlink("/sites/cenhtor/tmp/tempo/".$duo[0].".csv");
            }
        }
        return $collection;
    }

    function checkCollection($infos){
        $url = 'https://'.$infos['host'].'/api/workspaces/'.$infos['wskey'].'/elements';
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($request, CURLOPT_USERPWD, $infos['user'].":".$infos['pswd']); 
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $infos);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($request);
        //echo $response."<br>";
        curl_close($request);
    }

    function postOrtolang($infos,$list_files){//upload files
        $url = 'https://'.$infos['host'].'/api/workspaces/'.$infos['wskey'].'/elements';
        $infos['type'] = 'object';
        $old_path = $infos['path'];
        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $url);
        curl_setopt($request, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($request, CURLOPT_USERPWD, $infos['user'].":".$infos['pswd']);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        foreach ($list_files as $filename) {
            if (explode(".", $filename)[1] == "csv") {//send the metadata file
                $handle = fopen('/sites/cenhtor/tmp/tempo/'.$filename, "rb");
                $contents = fread($handle, filesize('/sites/cenhtor/tmp/tempo/'.$filename));
                fclose($handle);
                $infos['path'] = $old_path.'/'.$filename;
                $infos['stream'] = $contents;
                curl_setopt($request, CURLOPT_POSTFIELDS, $infos);
                $response = curl_exec($request);
                foreach ($list_files as $filename2){//send the associated file
                    if (explode(".", $filename)[0] == explode(".", $filename2)[0] && explode(".", $filename)[1] != explode(".", $filename2)[1]) {
                        $handle = fopen('/sites/cenhtor/tmp/tempo/'.$filename2, "rb");
                        $contents = fread($handle, filesize('/sites/cenhtor/tmp/tempo/'.$filename2));
                        fclose($handle);
                        $infos['path'] = $old_path.'/'.$filename2;
                        $infos['stream'] = $contents;
                        curl_setopt($request, CURLOPT_POSTFIELDS, $infos);
                        $response = curl_exec($request);
                    }
                }
            }
        }
        echo $response;
        curl_close($request);
    }

    function postNakala($infos, $list_files){//build a zip file and send it to Nakala
        foreach ($list_files as $filename) {
            if (explode(".", $filename)[1] == "csv") {//zip the metadata file
                $zip = new ZipArchive;
                $zip -> open('/sites/cenhtor/tmp/tempo/'.preg_replace("#[^a-zA-Z]#", "", explode(".", $filename)[0]).'.zip', ZipArchive::CREATE);
                $zip -> addFile('/sites/cenhtor/tmp/tempo/'.$filename, $filename);
                $curl_request = '';
                foreach ($list_files as $filename2){//zip the associated file
                    if (explode(".", $filename)[0] == explode(".", $filename2)[0] && explode(".", $filename)[1] != explode(".", $filename2)[1]) {
                        $zip->addFile('/sites/cenhtor/tmp/tempo/'.$filename2, $filename2);
                    }
                }
                $zip -> close();
                $infos['file_path'] = '/sites/cenhtor/tmp/tempo/'.preg_replace("#[^a-zA-Z]#", "", explode(".", $filename)[0]).'.zip';
                $curl_request = 'curl -H "Content-Type: application/octet-stream" -X POST --data-binary @'.$infos['file_path'].' "https://www.nakala.fr//nakala/api/v1/data?email='.$infos['email'].'&key='.$infos['key'].'&project='.$infos['project'].'"';
                $output = array();
                exec($curl_request, $output, $return_var);
                //echo "\n".$curl_request;
                //echo $filename."\n";
                print_r($output[0]);
                if (strpos($output[0], "Cette donnée existe déjà dans Nakala") !== false || strpos($output[0], "Le fichier zip ne contient pas de fichier de donnée") !==false){
                    unlink('/sites/cenhtor/tmp/tempo/'.preg_replace("#[^a-zA-Z]#", "", explode(".", $filename)[0]).'.zip');
                    $zip = new ZipArchive;
                    $zip -> open('/sites/cenhtor/tmp/tempo/'.preg_replace("#[^a-zA-Z]#", "", explode(".", $filename)[0]).'.zip', ZipArchive::CREATE);
                    $zip -> addFile('/sites/cenhtor/tmp/tempo/'.$filename, $filename);
                    $zip -> close();
                    $infos['file_path'] = '/sites/cenhtor/tmp/tempo/'.preg_replace("#[^a-zA-Z]#", "", explode(".", $filename)[0]).'.zip';
                    $curl_request = 'curl -H "Content-Type: application/octet-stream" -X PUT --data-binary @'.$infos['file_path'].' "https://www.nakala.fr/nakala/api/v1/data/'.getItem(explode(".", $filename)[0])['id'].'?email='.$infos['email'].'&key='.$infos['key'].'"';
                    $output = array();
                    exec($curl_request ,$output, $return_var);
                    print_r($output[0]);
                    /*echo $return_var."<br>";*/
                }
                unlink('/sites/cenhtor/tmp/tempo/'.preg_replace("#[^a-zA-Z]#", "", explode(".", $filename)[0]).'.zip');
            }
        } 
    }

    /*
    *   START
    */
    $list_files = array_slice(scandir('/sites/cenhtor/tmp/tempo'), 2);

    clear($list_files);

    openZip($_FILES["file"]["tmp_name"]);

    $list_files = array_slice(scandir('/sites/cenhtor/tmp/tempo'), 2);

    //echo "<br> VERIFICATION ... <br>";
    $valid_files = verifFiles($list_files);
    
    //echo "<br> COMPLETION DES METADONEES ... <br>";
    $collection = workMeta($valid_files, $list_files);

    $list_files = array_slice(scandir('/sites/cenhtor/tmp/tempo'), 2);

    //echo "<br> ENVOI A ORTOLANG ... <br>";
    
    $infos_Ortolang = array(
        'host' => 'repository.ortolang.fr',
        'wskey' => dbRetrive('ortolang', 'wskey'),
        'user' => dbRetrive('ortolang', 'user'),
        'pswd' => dbRetrive('ortolang', 'pswd'),
        'root' => 'head',
        'path' => 'Collections/'.$collection["name"],
        'type' => 'collection',
        'stream' => ''
    );
    checkCollection($infos_Ortolang);
    postOrtolang($infos_Ortolang, $list_files);

    //echo "<br> ENVOI A NAKALA ... <br>";
    $infos_Nakala = array(
        'email' => dbRetrive('nakala', 'email'),
        'key' => dbRetrive('nakala', 'key'),
        'file_path' => '',
        'project' => $collection['id']
    );
    postNakala($infos_Nakala, $list_files);

    //echo "<br> CLEAR ... <br>";
    clear($list_files);
    //echo "DONE";

    /**/
?>