<?php
/*******************************************************************************
@file					module.php

@author					Black-Blade  
@brief					YAVR modul
@date    				11.11.2023

@see http://host/YamahaRemoteControl/desc.xml
@see https://community.symcon.de/uploads/short-url/7r8QTdkYFNfJVJmKbtqvdleuzKt.pdf

*******************************************************************************/

if (!defined('__ROOT__'))  define('__ROOT__', dirname(dirname(__FILE__)));
if (!defined('__IMGS__'))  define('__IMGS__', __ROOT__."/imgs/");


class YAVR extends IPSModule {
/*******************************************************************************
@author					Black-Blade
@brief					ApplyChanges
@info                   Überschreibt die interne IPS_Create($id) Funktion
@date    				11.11.2023
*******************************************************************************/
    // 
    public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();

        $this->SendDebug("create","modul will create",0);
        $this->RegisterPropertyString('var_host', '');
        $this->RegisterPropertyInteger('var_port', 80);
        $this->RegisterPropertyString('var_zone', 'Main_Zone');
        $this->RegisterPropertyInteger('var_updateinterval', 5);
        $this->RegisterPropertyBoolean('var_debug', false);
        $this->RegisterTimer('Update', 0 ,get_class($this)."_GetStatus(".$this->InstanceID.");");
        $this->SendDebug("create","modul is create",0);   
    }

/*******************************************************************************
@author					Black-Blade
@brief					ApplyChanges
@info                   Überschreibt die intere IPS_ApplyChanges($id) Funktion
@date    				11.11.2023
*******************************************************************************/
    public function ApplyChanges() {
        // Diese Zeile nicht löschen
        parent::ApplyChanges();
        $this->SendDebug("applychanges","modul will applychanges",0);
       
        $zone = $this->ReadPropertyString('var_zone');

        if (!IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_inputs"))              IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_inputs", 1);
        if (!IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_sound_program_list"))  IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_program_list", 1);
        if (!IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list"))   IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list", 1);
        if (!IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_sound_power_list"))    IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_power_list", 1);
        if (!IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_cursor"))              IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor", 1);
        if (!IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_cursor_control"))      IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor_control",1);
        if (!IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_sleep"))               IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sleep",1);
        if (!IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_radioband"))           IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_radioband",1);

        $this->CreateYAVR_Profile();


        $this->RegisterVariableInteger("power", $this->Translate("power"),"YAVR_{$this->InstanceID}_{$zone}_sound_power_list",0);
        $this->RegisterVariableInteger("sleep", $this->Translate("slepp"),"YAVR_{$this->InstanceID}_{$zone}_sleep",1);
        $this->RegisterVariableBoolean("enhancer", $this->Translate("enhancer"),"~Switch",2);
        $this->RegisterVariableBoolean("online", $this->Translate("online"),"~Switch",2);
        
        $this->RegisterVariableInteger("input", $this->Translate("input"), "YAVR_{$this->InstanceID}_{$zone}_inputs",10);        
    
        $this->RegisterVariableBoolean("party", $this->Translate("party"), "~Switch",20);
        $this->RegisterVariableBoolean("surround_ai", $this->Translate("surround ai"),"~Switch",21);
        $this->RegisterVariableInteger("sound_program_list", $this->Translate("dsp"), "YAVR_{$this->InstanceID}_{$zone}_sound_program_list",22);
        $this->RegisterVariableInteger("surr_decoder", $this->Translate("surround decoder"), "YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list",23);#

        $this->RegisterVariableInteger("cursor",$this->Translate("cursor"), "YAVR_{$this->InstanceID}_{$zone}_cursor",30);
        $this->RegisterVariableInteger("cursor_control", $this->Translate("cursor control"), "YAVR_{$this->InstanceID}_{$zone}_cursor_control",30);

        $this->RegisterVariableInteger("volume", $this->Translate("volume"),"~Volume",40);
        $this->RegisterVariableBoolean("mute",$this->Translate("mute"), "~Mute",41); 
        
        $this->RegisterVariableInteger("playback", $this->Translate("playback"),"~PlaybackPreviousNext",50);
        $this->RegisterVariableInteger("repeat",$this->Translate("repeat"),"~Repeat",51);
        $this->RegisterVariableBoolean("shuffle", $this->Translate("shuffle"),"~Shuffle",52);
        $this->RegisterVariableFloat("playtime", $this->Translate("playtime"),"~Progress",53);

        $this->RegisterVariableString("artist", $this->Translate("artist"),"~Artist",60);
        $this->RegisterVariableString("album", $this->Translate("album"),"",61);
        $this->RegisterVariableString("track", $this->Translate("track"),"~Song",62);
        if ((@$this->GetIDForIdent('cover') === false)) {
            $coverID = IPS_CreateMedia(1);
            IPS_SetParent($coverID, $this->InstanceID);
            IPS_SetName($coverID, $this->Translate("current cover"));
            IPS_SetIdent($coverID, 'cover');
            IPS_SetMediaFile($coverID, 'cover.' . $this->InstanceID . '.jpg', false);
            IPS_SetPosition($coverID,63);
            IPS_SetMediaContent($coverID, '');
        }
        
        $this->RegisterVariableInteger("radioband", $this->Translate("radio band"), "YAVR_{$this->InstanceID}_{$zone}_radioband",70);
        $this->RegisterVariableInteger("freq", $this->Translate("frequency"), "",71);
        $this->RegisterVariableInteger("preset",$this->Translate("memory"), "",72);

        $this->RegisterVariableBoolean("headphone",$this->Translate("headphone"), "~Switch",80); 

        
        $this->EnableAction("mute");
        $this->EnableAction("volume");
        $this->EnableAction("playback");
        $this->EnableAction("repeat");
        $this->EnableAction("shuffle");
        $this->EnableAction("surround_ai");
        $this->EnableAction("cursor");
        $this->EnableAction("cursor_control");
        $this->EnableAction("party");
        $this->EnableAction("preset");
        $this->EnableAction("enhancer");
        
        
        
        $this->SetBuffer($this->InstanceID."_input", '');
        $this->SetBuffer($this->InstanceID."_coverurl", '');
        $this->SetBuffer($this->InstanceID."_radioband", '');


        $this->SetTimerInterval('Update', $this->ReadPropertyInteger('var_updateinterval') * 1000);

        $this->SendDebug("applychanges","modul is applychanges",0);
     
    }

/*******************************************************************************
@author					Black-Blade
@brief					Destroy
@info                   Wird ausgeführt wenn Instanz gelöscht wird
@date    				11.11.2023
*******************************************************************************/
    public function Destroy() {
         // Diese Zeile nicht löschen
        parent::Destroy();
        $this->SendDebug("destroy","modul will destroy",0);
        $zone = $this->ReadPropertyString('var_zone');

        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_inputs"))               IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_inputs");
        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_sound_program_list"))   IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_program_list");
        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_sound_power_list"))     IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_power_list", 1);  
        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list"))    IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list");
        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_cursor"))               IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor");
        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_cursor_control"))       IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor_control");
        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_sleep"))                IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sleep");
        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_radioband"))            IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_radioband",1);

        $this->SendDebug("destroy","modul is destroy",0);
    }
    
/*******************************************************************************
@author					Black-Blade
@brief					RequestAction
@info                   Auswerten von RequestAction
@param                  Ident -> IdentVariabel
@param                  Value ->  wert übergeben
@date    				11.11.2023
*******************************************************************************/
    public function RequestAction($Ident, $Value)
    {
        $this->SendDebug("requestaction","{$Ident} set {$Value}",0);

        switch ($Ident) {
            case 'power':
				$this->SetPower($Value);
				break;

            case 'sleep':
                $this->SetSleep($Value);
                break;

            case 'sound_program_list':
				$this->SetSoundProgram($Value);
				break;

            case 'input':
				$this->SetInput($Value);
				break;

            case 'mute':
			    $this->SetMute($Value);		
                break;
            
            case 'volume':
                $this->SetVolume($Value);
                break;

            case 'playback':
                $this->SetPlayback($Value);
                break;

            case 'repeat':
                $this->SetRepeat($Value);
                break;
        
            case 'shuffle':
                $this->SetShuffle($Value);
                break;

            case 'surround_ai':
                $this->SetSurround_AI($Value);
                break;
    
            case 'surr_decoder':
                $this->SetSurr_Decoder($Value);
                break;

            case 'cursor':
                $this->SetCursor($Value);
                break;

            case 'cursor_control':
                $this->SetCursor_Control($Value);
                break;
            case 'party':
                $this->SetPartyMode($Value);
                break;

            case 'radioband':
                $this->SetRadioband($Value);
                break;

            case 'preset':
                $this->SetPreset($Value);
                break;

            case 'enhancer':
                $this->SetEnhancer($Value);
                break;
            
            /*
            case 'hdmi_out_1':
                $this->SetHdmi_out_1($Value);
                break;

            case 'hdmi_out_2':
                $this->SetHdmi_out_2($Value);
                break;

            case 'hdmi_out_3':
                $this->SetHdmi_out_3($Value);
                break;
            */          
            
          }
    }

/*******************************************************************************
@author					Black-Blade
@brief					RequestPicture
@info                   Lädt ein URL von Bild in Cover
@param                  pictureurl -> URL des Bildes
@date    				11.11.2023
*******************************************************************************/
    private function RequestPicture(string $pictureurl) {
    
        $Bufferdata = $this->GetBuffer($this->InstanceID."_coverurl");
        if ($Bufferdata == $pictureurl) return;
     
        $this->SendDebug("requestpicture","{$pictureurl} will set",0);
        $this->SetBuffer($this->InstanceID."_coverurl",$pictureurl);

        $host = $this->ReadPropertyString('var_host');
        $port = $this->ReadPropertyInteger('var_port');

        if(!$host) {
            $this->SetStatus(203);
            $this->SendDebug("requestpicture", $this->Translate("yavr error"),0);
            return false;
        }

        $client = curl_init();
        curl_setopt($client, CURLOPT_URL, "http://$host:$port$pictureurl");
        curl_setopt($client, CURLOPT_USERAGENT, "SymconYAVR");
        curl_setopt($client, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($client, CURLOPT_TIMEOUT, 5);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($client);
        $status = curl_getinfo($client, CURLINFO_HTTP_CODE);
        curl_close($client);

        if ($status != '200') {
            $this->SetStatus(202);
            $this->SendDebug("requestpicture", $this->Translate("unknown error occurred"),0);
            return false;
        } 
        $coverfile = IPS_GetKernelDir().'cover.' . $this->InstanceID . '.jpg';
        $myfile = fopen($coverfile, "wb") or die("Unable to open file!");
        fwrite($myfile, $result);
        fclose($myfile);

        $coverID = $this->GetIDForIdent('cover');
        IPS_SetMediaFile($coverID, 'cover.' . $this->InstanceID . '.jpg', false);
        $this->SendDebug("requestpicture","{$pictureurl} is set",0);
    }

/*******************************************************************************
@author					Black-Blade
@brief					CopyPicture
@info                   Lädt ein Standartbild in Cover
@param                  pic -> Name des Bildes
@date    				11.11.2023
*******************************************************************************/
    private function CopyPicture(string $pic) {

        $Bufferdata = $this->GetBuffer($this->InstanceID."_coverurl");
        if ($Bufferdata == $pic) return;
        $this->SendDebug("copypicture","{$pic} will copy",0);
        $this->SetBuffer($this->InstanceID."_coverurl",$pic);

        $coverfile = IPS_GetKernelDir().'cover.' . $this->InstanceID . '.jpg';
        $pic=__IMGS__.$pic;
        @copy($pic,$coverfile);
        $coverID = $this->GetIDForIdent('cover');
        IPS_SetMediaFile($coverID, 'cover.' . $this->InstanceID . '.jpg', false);
        $this->SendDebug("copypicture","{$pic} is copy",0);
    }
    
/*******************************************************************************
@author					Black-Blade
@brief					RequestJSON
@info                   Anfage über JSON
@param                  method -> setSoundProgram
@param                  system -> Anfrage an das System sonst Zone
@date    				11.11.2023
*******************************************************************************/
    public function RequestJSON(string $method,bool $system = false) {
        $this->SendDebug("requestjson","{$method} and {$system}",0);
        $zone = $this->ReadPropertyString('var_zone');
        $zoneMapper = array(
            'Main_Zone' => 'main',
            'Zone_2' => 'zone2',
            'Zone_3' => 'zone3',
            'Zone_4' => 'zone4'
        );

        $zone = $zoneMapper[$zone];
        $zone_request = $system ? 'system' : $zone;
        $result= $this->RequestJSONex($zone_request,$method,$system);

        if ($system && isset($result->zone)) {
            foreach ($result->zone AS $zone_data) {
                if ($zone_data->id == $zone) {
                    $result->zone = $zone_data;
                    break;
                }
            }
        }
        return  $result;
    }

/*******************************************************************************
@author					Black-Blade
@brief					RequestJSON
@info                   Anfage über JSON
@param                  zone -> welche Zone oder Programm z.B "Spotify"
@param                  method -> setSoundProgram
@param                  system -> Anfrage an das System sonst Zone
@date    				11.11.2023
*******************************************************************************/
    public function RequestJSONex(string $zone,string $method) {
        $this->SendDebug("requestjsonex","{$zone} and {$method}",0);
        $host = $this->ReadPropertyString('var_host');
        $port = $this->ReadPropertyInteger('var_port');

        if(!$host) {
            $this->SetValue( "online", false);
            $this->SendDebug("requestjsonex", $this->Translate("yavr error"),0);
            $this->SetStatus(203);
            return false;
        }

        $this->SendDebug("requestjsonex", "send http://$host:$port/YamahaExtendedControl/v1/$zone/$method",0);
        $client = curl_init();
        curl_setopt($client, CURLOPT_URL, "http://$host:$port/YamahaExtendedControl/v1/$zone/$method");
        curl_setopt($client, CURLOPT_USERAGENT, "SymconYAVR");
        curl_setopt($client, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($client, CURLOPT_TIMEOUT, 5);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($client);
        $status = curl_getinfo($client, CURLINFO_HTTP_CODE);
        curl_close($client);

        $result = @json_decode($result);

            
        
        if ($status == '0') {
            $this->SetValue( "online", false);
            $this->SendDebug("requestjsonex", $this->Translate("yavr is not available"),0);
            $this->SetStatus(201);
            return false;
        } elseif ($status != '200') {
            $this->SetValue( "online", false);
            $this->SendDebug("requestjsonex", $this->Translate("unknown error occurred"),0);
            $this->SetStatus(202);
            return false;
        } else {
            $this->SetValue( "online", true);
            $this->SendDebug("requestjsonex", $this->Translate("yavr is aktiv"),0);
            $this->SetStatus(102);
            return $result;
        }
    }

/*******************************************************************************
@author					Black-Blade
@brief					RequestXML
@info                   Anfage über XML 
@param                  partial -> "<Play_Info>GetParam</Play_Info>"
@param                  cmd -> PUT oder GET
@date    				11.11.2023
*******************************************************************************/
    public function RequestXML(string $partial,string $cmd = 'GET')
    {
        $this->SendDebug("requestxml","{$partial} and {$cmd}",0);
        $zone = $this->ReadPropertyString('var_zone');
        return $this->RequestXMLex($zone,$partial, $cmd);
    }

/*******************************************************************************
@author					Black-Blade
@brief					RequestXML
@info                   Anfage über XML 
@param                  zone -> weleche zone oder programm z.B "Spotify"
@param                  partial -> "<Play_Info>GetParam</Play_Info>"
@param                  cmd -> PUT oder GET
@date    				11.11.2023
*******************************************************************************/	   
    public function RequestXMLex(string $zone,string $partial,string  $cmd = 'GET') {
        $this->SendDebug("requestxmlex","{$zone} and {$partial} and {$cmd}",0);
        $host = $this->ReadPropertyString('var_host');
        $port = $this->ReadPropertyInteger('var_port');
      
        if(!$host) {
            $this->SetValue( "online", false);
            $this->SendDebug("requestxmlex", $this->Translate("yavr error"),0);
            $this->SetStatus(203);
            return false;
        }

        $cmd = strtoupper($cmd);
        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
        $xml .= "<YAMAHA_AV cmd=\"{$cmd}\">";
        $xml .= "<{$zone}>{$partial}</{$zone}>";
        $xml .= "</YAMAHA_AV>";

        $this->SendDebug("requestxmlex", "send http://$host:$port/YamahaRemoteControl/ctrl",0);
        $this->SendDebug("requestxmlex", "post $xml",0);
       
        $client = curl_init();
        curl_setopt($client, CURLOPT_URL, "http://$host:$port/YamahaRemoteControl/ctrl");
        curl_setopt($client, CURLOPT_USERAGENT, "SymconYAVR");
        curl_setopt($client, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($client, CURLOPT_TIMEOUT, 5);
        curl_setopt($client, CURLOPT_POST, true);
        curl_setopt($client, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($client, CURLOPT_POSTFIELDS, $xml);
        $result = curl_exec($client);
        $status = curl_getinfo($client, CURLINFO_HTTP_CODE);
        curl_close($client);

        if ($status == '0') {
            $this->SetStatus(201);
            $this->SetValue( "online", false);
            $this->SendDebug("requestxmlex", $this->Translate("yavr is not available"),0);
            return false;
        } elseif ($status != '200') {
            $this->SetStatus(202);
            $this->SetValue( "online", false);
            $this->SendDebug("requestxmlex", $this->Translate("unknown error occurred"),0);
            return false;
        } else {
            $this->SetStatus(102);
            $this->SetValue( "online", true);
            $this->SendDebug("requestxmlex", $this->Translate("yavr is aktiv"),0);
            if($cmd == 'PUT') return true;
            return simplexml_load_string($result)->$zone;
        }
    }

/*******************************************************************************
@author					Black-Blade
@brief					CreateYAVR_Profile
@info                   Erstelt die Eingags und Sound Profile des YAVR
@date    				11.11.2023
*******************************************************************************/	
    private function CreateYAVR_Profile()
    {
        $this->SendDebug("createyavr_profile", "will create profil",0);
        $zone = $this->ReadPropertyString('var_zone');
       
        
        // erstelle eine neue liste von type YAVR_{$this->InstanceID}_{$zone}_sound_power_list"

        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_sound_power_list"))    IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_power_list");
        IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_power_list", 1);
        $this->DisableAction("power");
        
        IPS_SetVariableProfileIcon ("YAVR_{$this->InstanceID}_{$zone}_sound_power_list", "Power"); 
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_sound_power_list", 0, "on", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_sound_power_list", 1,"standby", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_sound_power_list", 2, "toggle", '', -1);
    
        $this->EnableAction("power");

        // erstelle eine neue liste von type YAVR_{$this->InstanceID}_{$zone}_sleep"

        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_sleep"))    IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sleep");
        IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sleep", 1);
        $this->DisableAction("sleep");
        
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_sleep", 0, "0", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_sleep", 1, "30", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_sleep", 2,"60", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_sleep", 3, "90", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_sleep", 4, "120", '', -1);
        $this->EnableAction("sleep");

        // erstelle eine neue liste von type YAVR_{$this->InstanceID}_{$zone}_cursor"

        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_cursor"))    IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor");
        IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor", 1);
        $this->DisableAction("cursor");
        
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor", 0, "Up", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor", 1,"Down", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor", 2, "Left", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor", 3, "Right", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor", 4,"Return", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor", 5, "Sel", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor", 6, "Return to Home", '', -1);
        $this->EnableAction("cursor");


           // erstelle eine neue liste von type YAVR_{$this->InstanceID}_{$zone}_cursor_control"
        if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_cursor_control"))    IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor_control");
        IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 1);
        $this->DisableAction("cursor_control");
        
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 0,"On Screen", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 1,"Top Menu", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 2,"Cursor_Menu", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 3,"Menu", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 4,"Option", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 5,"Help", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 6,"Red", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 7,"Green", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 8,"Blue", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 9,"Yellow", '', -1);
        IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_cursor_control", 10,"Home", '', -1);

        $this->EnableAction("cursor_control");

           // erstelle eine neue liste von type YAVR_{$this->InstanceID}_{$zone}_radioband"
           if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_radioband"))    IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_radioband");
           IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_radioband", 1);
           $this->DisableAction("radioband");
           
           IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_radioband", 0,"am", '', -1);
           IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_radioband", 1,"fm", '', -1);
           IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_radioband", 2,"dab", '', -1);
   
           $this->EnableAction("radioband");

        @$data = $this->RequestJSON("getFeatures",true)->zone;
        if (!$data === false)
        {
            
            // erstelle eine neue liste von type YAVR_{$this->InstanceID}_{$zone}_inputs"
            $list = $data->input_list;
            if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_inputs")) IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_inputs");
            IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_inputs", 1);
            $this->DisableAction("input");

            if (@count($list) > 0) 
            {
       
                foreach ($list as $key => $name) 
                {
                    IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_inputs", $key, $name, '', -1);
                }
            }
            $this->EnableAction("input");
            
            // erstelle eine neue liste von type YAVR_{$this->InstanceID}_{$zone}_sound_program_list"
            // info wird nicht verwendend wenn sie auf zone 2- 4 sind jedenfall bei meinen YAVR

            @$list = $data->sound_program_list;
            if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_sound_program_list")) IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_program_list");
            IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_program_list", 1);
            $this->DisableAction("sound_program_list");
    
            if (($list != null) && (@count($list)) > 0) {
          
                foreach ($list as $key => $name) {
                    IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_sound_program_list", $key, $name, '', -1);
                }
                $this->EnableAction("sound_program_list");
            }
           
            // erstelle eine neue liste von type YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list"
            // info wird nicht verwendend wenn sie auf zone 2- 4 sind jedenfall bei meinen YAVR

            @$list = $data->surr_decoder_type_list;
            if (IPS_VariableProfileExists("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list")) IPS_DeleteVariableProfile("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list");
            IPS_CreateVariableProfile("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list", 1);
            $this->DisableAction("surr_decoder");
    
            if (($list != null) && (@count($list)) > 0) {
          
                foreach ($list as $key => $name) {
                    IPS_SetVariableProfileAssociation("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list", $key, $name, '', -1);
                }
                $this->EnableAction("surr_decoder");
            }
          
            $this->SendDebug("createyavr_profile", "profil is create",0);
        }
    }

/*******************************************************************************
@author					Black-Blade
@brief					GetStaus
@info                   Anworten vom YAVR auswerten und in Variabel schreiben
@date    				11.11.2023
*******************************************************************************/	
    public function GetStatus()
    {
        $this->SendDebug("getstatus","read",0);
        $this->GetStatus_System();

        $zone = $this->ReadPropertyString('var_zone');
        @$data = $this->RequestJSON("getStatus",false);
        // Schalter Power
        @$power =$data->power;
        if ($power!=null)
        {
            $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_power_list")["Associations"];
            if (@count($list) > 0) 
            {
                foreach ($list as $programm)
                {
                    if ($programm["Name"]==$power) 
                    {
                        $this->SetValue( "power", $programm["Value"]);
                    }
                }
            }        
        }
        else
        {
            $this->SetValue( "power", -1);    
        }

        // Schalter sleep
        @$sleep =$data->sleep;
        $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sleep")["Associations"];
        if (@count($list) > 0) 
        {
            foreach ($list as $programm)
            {
                if ($programm["Name"]==$sleep) 
                {
                    $this->SetValue( "sleep", $programm["Value"]);
                }
            }
        }        

        // Schaltet den surround_ai  Status um
        if (!$this->GetValue( "party"))
        {
            @$surround_ai =$data->surround_ai;    
            if ($surround_ai!=null)
            {
            
                IPS_SetDisabled($this->GetIDForIdent('sound_program_list'),true);
                IPS_SetHidden($this->GetIDForIdent('sound_program_list'),true);
                $this->EnableAction("sound_program_list");
                            
                IPS_SetDisabled($this->GetIDForIdent('surr_decoder'),true);
                IPS_SetHidden($this->GetIDForIdent('surr_decoder'),true);
                $this->EnableAction("surr_decoder");
            
                if ($surround_ai==1) $this->SetValue( "surround_ai", true);
                //else $this->SetValue( "surround_ai", false);
            }
            else
            {
                IPS_SetDisabled($this->GetIDForIdent('sound_program_list'),false);
                IPS_SetHidden($this->GetIDForIdent('sound_program_list'),false);
                $this->EnableAction("sound_program_list");
                            
                IPS_SetDisabled($this->GetIDForIdent('surr_decoder'),false);
                IPS_SetHidden($this->GetIDForIdent('surr_decoder'),false);
                $this->EnableAction("surr_decoder");

                $this->SetValue( "surround_ai", false);
            }
        }
        else
        {
            IPS_SetDisabled($this->GetIDForIdent('sound_program_list'),true);
            IPS_SetHidden($this->GetIDForIdent('sound_program_list'),true);
            $this->EnableAction("sound_program_list");
                        
            IPS_SetDisabled($this->GetIDForIdent('surr_decoder'),true);
            IPS_SetHidden($this->GetIDForIdent('surr_decoder'),true);
            $this->EnableAction("surr_decoder");
        }
        // Schaltet den Sound Status um
        @$sound_program =$data->sound_program;
        if ((!$this->GetValue( "party")) && (!$this->GetValue( "surround_ai")))
        {
            if ($sound_program!=null)
            {
                IPS_SetDisabled($this->GetIDForIdent('sound_program_list'),false);
                IPS_SetHidden($this->GetIDForIdent('sound_program_list'),false); 
                
                $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_program_list")["Associations"];
                if (@count($list) > 0) 
                {
                    foreach ($list as $programm)
                    {
                        if ($programm["Name"]==$sound_program) 
                        {
                            $this->SetValue( "sound_program_list", $programm["Value"]);
                        }
                    }
                }
            }
            else
            {
                $this->SetValue( "sound_program_list", -1); 
                IPS_SetDisabled($this->GetIDForIdent('sound_program_list'),true); 
                IPS_SetHidden($this->GetIDForIdent('sound_program_list'),true);
                
            }
        }

        // Schaltet den Surround Status um
        if ((!$this->GetValue( "party")) && (!$this->GetValue( "surround_ai")))
        {
            @$surr_decoder_type =$data->surr_decoder_type;
            if ($surr_decoder_type!=null)
            {
                IPS_SetDisabled($this->GetIDForIdent('surr_decoder'),false);
                IPS_SetHidden($this->GetIDForIdent('surr_decoder'),false); 
                
                $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list")["Associations"];
                if (@count($list) > 0) 
                {
                    foreach ($list as $programm)
                    {
                        if ($programm["Name"]==$surr_decoder_type) 
                        {
                            $this->SetValue( "surr_decoder", $programm["Value"]);
                        }
                    }
                }
            }
            else
            {
                $this->SetValue( "surr_decoder", -1); 
                IPS_SetDisabled($this->GetIDForIdent('surr_decoder'),true); 
                IPS_SetHidden($this->GetIDForIdent('surr_decoder'),true);
                
            }
        } 

        // Schaltet den Input Status um
        @$input =$data->input;
        if ($input!=null)
        {
            $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_inputs")["Associations"];
            if (@count($list) > 0) 
            {
                foreach ($list as $programm)
                {
                    if ($programm["Name"]==$input) 
                    {
                        $this->SetBuffer($this->InstanceID."_input", $input);
                        $this->SetValue( "input", $programm["Value"]);
                    }
                }
            }
        }
        else
        {
            $this->SetBuffer($this->InstanceID."_input", '');
            $this->SetValue( "input", -1);
                  
        }
        
        // Schaltet den Mute Status um
        @$mute =$data->mute;
        if ($mute!=null)
        {
            if ($mute==1) $this->SetValue( "mute", true);
            else $this->SetValue( "mute", false);
        }
        else
        {
            $this->SetValue( "mute", false);
        }

        //Schalter Volume;
        @$volume = $data->volume;
        if ($volume!=null)
        {
            @$volume = round(100/160*$volume,0);
            $this->SetValue ( "volume", $volume);
        }
        else
        {
            $this->SetValue ( "volume", -1);    
        }
        
        // Schaltet den Enhancer Status um
        @$enhancer =$data->enhancer;
        if ($enhancer!=null)
        {
            if ($enhancer==1) $this->SetValue( "enhancer", true);
            else $this->SetValue( "enhancer", false);
        }
        else
        {
            $this->SetValue( "enhancer", false);
        }

        if (str_contains($input, 'av')) $this->GetStatus_AV($data);
        else if (str_contains($input, 'tuner')) $this->GetStatus_Tuner();
        else if ((str_contains($input, 'audio')) || (str_contains($input, 'aux'))) $this->GetStatus_Audio($data);
        else if (str_contains($input, 'phono')) $this->GetStatus_Phono($data);
        else $this->GetStatus_NetUSB();

    }

/*******************************************************************************
@author					Black-Blade
@brief					GetStatus_System
@info                   anworten vom YAVR Systen auswerten und in Variabel schreiben
@date    				11.11.2023
*******************************************************************************/	
    private function GetStatus_System()
    {
        $this->SendDebug("getstatus_system","read",0);
      
        @$data = $this->RequestJSON("getFuncStatus",true);
        // parymodus laden
        @$party_mode = $data->party_mode;
        if ($party_mode !=null) 
        {
            IPS_SetDisabled($this->GetIDForIdent('surround_ai'),true);
            IPS_SetHidden($this->GetIDForIdent('surround_ai'),true);
            $this->DisableAction("surround_ai");
            $this->SetValue( "party", true);  
        }
        else
        {
            IPS_SetDisabled($this->GetIDForIdent('surround_ai'),false);
            IPS_SetHidden($this->GetIDForIdent('surround_ai'),false);
            $this->EnableAction("surround_ai");
            $this->SetValue( "party", false);
        }
    }

/*******************************************************************************
@author					Black-Blade
@brief					GetStatus_AV
@info                   Anworten vom YAVR auswerten und in Variabel schreiben
@param                  data -> Daten von der zone
@date    				11.11.2023
*******************************************************************************/	
    private function GetStatus_AV($data)
    {
        $this->SendDebug("getstatus_av","read",0);

        IPS_SetDisabled($this->GetIDForIdent('repeat'),true);
        IPS_SetHidden($this->GetIDForIdent('repeat'),true);
        $this->DisableAction("repeat");
                
        IPS_SetDisabled($this->GetIDForIdent('shuffle'),true);
        IPS_SetHidden($this->GetIDForIdent('shuffle'),true);
        $this->DisableAction("shuffle");

        IPS_SetDisabled($this->GetIDForIdent('playtime'),true);
        IPS_SetHidden($this->GetIDForIdent('playtime'),true);
        $this->DisableAction("playtime");

        IPS_SetDisabled($this->GetIDForIdent('radioband'),true);
        IPS_SetHidden($this->GetIDForIdent('radioband'),true);

        IPS_SetDisabled($this->GetIDForIdent('freq'),true);
        IPS_SetHidden($this->GetIDForIdent('freq'),true);
    
        IPS_SetDisabled($this->GetIDForIdent('preset'),true);
        IPS_SetHidden($this->GetIDForIdent('preset'),true);
        $this->DisableAction("preset");
        
        // playback
        $this->SetValue( "playback", 1); 
        
            // lade neuse picture von yavr
        $this->CopyPicture("hdmi.png");

            // repat ladem
            $this->SetValue( "repeat", 0);

            // shuffle laden
        $this->SetValue( "shuffle", false);

        // artist laden
        @$artist = $data->input;
        if ($artist !=null) 
        {
            $this->SetValue( "artist", $artist);
        }
        else
        {
            $this->SetValue( "artist", "");
        }

        // album laden
        $this->SetValue( "album", "");

        // track laden
        @$track = $data->input_text;
        if ($track !=null) 
        {
            $this->SetValue( "track", $track);
        }
        else
        {
            $this->SetValue( "track", "");     
        }

        // play_time laden
        $this->SetValue( "playtime", 0);
    }

/*******************************************************************************
@author					Black-Blade
@brief					GetStatus_Audio
@info                   anworten vom YAVR auswerten und in Variabel schreiben
@param                  data -> Daten von der zone
@date    				11.11.2023
*******************************************************************************/	
    private function GetStatus_Audio($data)
    {
        $this->SendDebug("getstatus_audio","read",0);

        IPS_SetDisabled($this->GetIDForIdent('repeat'),true);
        IPS_SetHidden($this->GetIDForIdent('repeat'),true);
        $this->DisableAction("repeat");
                
        IPS_SetDisabled($this->GetIDForIdent('shuffle'),true);
        IPS_SetHidden($this->GetIDForIdent('shuffle'),true);
        $this->DisableAction("shuffle");

        IPS_SetDisabled($this->GetIDForIdent('playtime'),true);
        IPS_SetHidden($this->GetIDForIdent('playtime'),true);
        $this->DisableAction("playtime");

        IPS_SetDisabled($this->GetIDForIdent('radioband'),true);
        IPS_SetHidden($this->GetIDForIdent('radioband'),true);

        IPS_SetDisabled($this->GetIDForIdent('freq'),true);
        IPS_SetHidden($this->GetIDForIdent('freq'),true);
    
        IPS_SetDisabled($this->GetIDForIdent('preset'),true);
        IPS_SetHidden($this->GetIDForIdent('preset'),true);
        $this->DisableAction("preset");

        // playback
        $this->SetValue( "playback", 1); 
        
            // lade neuse picture von yavr
        $this->CopyPicture("audio.png");

            // repat ladem
            $this->SetValue( "repeat", 0);

            // shuffle laden
        $this->SetValue( "shuffle", false);

        // artist laden
        @$artist = $data->input;
        if ($artist !=null) 
        {
            $this->SetValue( "artist", $artist);
        }
        else
        {
            $this->SetValue( "artist", "");
        }

        // album laden
        $this->SetValue( "album", "");

        // track laden
        @$track = $data->input_text;
        if ($track !=null) 
        {
            $this->SetValue( "track", $track);
        }
        else
        {
            $this->SetValue( "track", "");     
        }

        // play_time laden
        $this->SetValue( "playtime", 0);
    }

/*******************************************************************************
@author					Black-Blade
@brief					GetStatus_Phone
@info                   anworten vom YAVR auswerten und in Variabel schreiben
@param                  data -> Daten von der zone
@date    				11.11.2023
*******************************************************************************/	
    private function GetStatus_Phono($data)
    {
        $this->SendDebug("getstatus_phono","read",0);
       
        IPS_SetDisabled($this->GetIDForIdent('repeat'),true);
        IPS_SetHidden($this->GetIDForIdent('repeat'),true);
        $this->DisableAction("repeat");
                
        IPS_SetDisabled($this->GetIDForIdent('shuffle'),true);
        IPS_SetHidden($this->GetIDForIdent('shuffle'),true);
        $this->DisableAction("shuffle");

        IPS_SetDisabled($this->GetIDForIdent('playtime'),true);
        IPS_SetHidden($this->GetIDForIdent('playtime'),true);
        $this->DisableAction("playtime");

        IPS_SetDisabled($this->GetIDForIdent('radioband'),true);
        IPS_SetHidden($this->GetIDForIdent('radioband'),true);

        IPS_SetDisabled($this->GetIDForIdent('freq'),true);
        IPS_SetHidden($this->GetIDForIdent('freq'),true);
    
        IPS_SetDisabled($this->GetIDForIdent('preset'),true);
        IPS_SetHidden($this->GetIDForIdent('preset'),true);
        $this->DisableAction("preset");

        $this->SendDebug("getstatus_phono","read",0);
        // playback
        $this->SetValue( "playback", 1); 
        
        // lade neuse picture von yavr
        $this->CopyPicture("phono.png");

        // repat ladem
        $this->SetValue( "repeat", 0);

        // shuffle laden
        $this->SetValue( "shuffle", false);

        // artist laden
        @$artist = $data->input;
        if ($artist !=null) 
        {
            $this->SetValue( "artist", $artist);
        }
        else
        {
            $this->SetValue( "artist", "");
        }

        // album laden
        $this->SetValue( "album", "");

        // track laden
        @$track = $data->input_text;
        if ($track !=null) 
        {
            $this->SetValue( "track", $track);
        }
        else
        {
            $this->SetValue( "track", "");     
        }

        // play_time laden
        $this->SetValue( "playtime", 0);
    }

/*******************************************************************************
@author					Black-Blade
@brief					GetStatus_NetUSB
@info                   Anworten vom YAVR auswerten und in Variabel schreiben
@date    				11.11.2023
*******************************************************************************/	
    private function GetStatus_NetUSB()
    {
        $this->SendDebug("getstatus_netusb","read",0);
        IPS_SetDisabled($this->GetIDForIdent('repeat'),false);
        IPS_SetHidden($this->GetIDForIdent('repeat'),false);
        $this->EnableAction("repeat");
                
        IPS_SetDisabled($this->GetIDForIdent('shuffle'),false);
        IPS_SetHidden($this->GetIDForIdent('shuffle'),false);
        $this->EnableAction("shuffle");

        IPS_SetDisabled($this->GetIDForIdent('playtime'),false);
        IPS_SetHidden($this->GetIDForIdent('playtime'),false);
        $this->EnableAction("playtime");

        IPS_SetDisabled($this->GetIDForIdent('radioband'),true);
        IPS_SetHidden($this->GetIDForIdent('radioband'),true);

        IPS_SetDisabled($this->GetIDForIdent('freq'),true);
        IPS_SetHidden($this->GetIDForIdent('freq'),true);
    
        IPS_SetDisabled($this->GetIDForIdent('preset'),true);
        IPS_SetHidden($this->GetIDForIdent('preset'),true);
        $this->DisableAction("preset");


        @$data = $this->RequestJSONex("netusb","getPlayInfo",false);
        // playback
        @$playback = $data->playback;
        if ($playback !=null) 
        {
            if ($playback =="previous" )  $this->SetValue( "playback", 0);
            if ($playback =="stop" )  $this->SetValue( "playback", 1);
            if ($playback =="play" )  $this->SetValue( "playback", 2);
            if ($playback =="pause" )  $this->SetValue( "playback", 3);
            if ($playback =="next" )  $this->SetValue( "playback", 4);
        }
        else
        {
            $this->SetValue( "playback", -1);
        }
        
        // lade neuse picture von yavr
        @$albumart_url = $data->albumart_url;
        if ($albumart_url !=null) 
        {
            $this->RequestPicture($albumart_url);
        }
        else
        {
            $this->CopyPicture("audio.png");
        }
        
        // repat ladem
        @$repeat = $data->repeat;
        if ($repeat !=null) 
        {
            if ($repeat =="off" )  $this->SetValue( "repeat", 0);
            if ($repeat =="all" )  $this->SetValue( "repeat", 1);
            if ($repeat =="one" )  $this->SetValue( "repeat", 2);
        }
        else
        {
            $this->SetValue( "repeat", -1);
        }

        // shuffle laden
        @$shuffle = $data->shuffle;
        if ($shuffle !=null) 
        {
            if ($shuffle=="off") $this->SetValue( "shuffle", false);
            else $this->SetValue( "shuffle", true);
        }
        else
        {
            $this->SetValue( "shuffle", false);
        }

        // artist laden
        @$artist = $data->artist;
        if ($artist !=null) 
        {
            $this->SetValue( "artist", $artist);
        }
        else
        {
            $this->SetValue( "artist", "");
        }

        // album laden
        @$album = $data->album;
        if ($album !=null) 
        {
            $this->SetValue( "album", $album);
        }
        else
        {
            $this->SetValue( "album", "");
        }

        // track laden
        @$track = $data->track;
        if ($track !=null) 
        {
            $this->SetValue( "track", $track);
        }
        else
        {
            $this->SetValue( "track", "");
        }

        // play_time laden
        @$play_time = $data->play_time;
        @$total_time = $data->total_time;

        if (($play_time !=null) && ($total_time !=null))
        {
            $play_time= 100/$total_time*$play_time;
            $this->SetValue( "playtime", $album);
        }
        else
        {
            $this->SetValue( "playtime", -1);
        }

    }

/*******************************************************************************
@author					Black-Blade
@brief					GetStatus_Tuner
@info                   Anworten vom YAVR auswerten und in Variabel schreiben
@date    				11.11.2023
*******************************************************************************/	
    private function GetStatus_Tuner()
    {
        $this->SendDebug("getstatus_tuner","read",0);
       
        IPS_SetDisabled($this->GetIDForIdent('repeat'),true);
        IPS_SetHidden($this->GetIDForIdent('repeat'),true);
        $this->DisableAction("repeat");
                
        IPS_SetDisabled($this->GetIDForIdent('shuffle'),true);
        IPS_SetHidden($this->GetIDForIdent('shuffle'),true);
        $this->DisableAction("shuffle");

        IPS_SetDisabled($this->GetIDForIdent('playtime'),true);
        IPS_SetHidden($this->GetIDForIdent('playtime'),true);
        $this->DisableAction("playtime");
   
        IPS_SetDisabled($this->GetIDForIdent('radioband'),false);
        IPS_SetHidden($this->GetIDForIdent('radioband'),false);

        IPS_SetDisabled($this->GetIDForIdent('freq'),false);
        IPS_SetHidden($this->GetIDForIdent('freq'),false);

        IPS_SetDisabled($this->GetIDForIdent('preset'),false);
        IPS_SetHidden($this->GetIDForIdent('preset'),false);
        $this->EnableAction("preset");
        
        $zone = $this->ReadPropertyString('var_zone');
        @$data = $this->RequestJSONex("tuner","getPlayInfo",false);

        // playback
        @$band = $data->band;
        if ($band !=null) 
        {
            $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_radioband")["Associations"];
            if (@count($list) > 0) 
            {
                foreach ($list as $programm)
                {
                    if ($programm["Name"]==$band) 
                    {
                        $this->SetValue( "radioband", $programm["Value"]);
                    }
                }
            }        
        }
        else
        {
            $this->SetValue( "radioband", -1);
        }
        
        // Setze buffer auf radioband
        $this->SetBuffer($this->InstanceID."_radioband", $band);
        
        // lade neuse picture von yavr
            $this->CopyPicture("tuner.png");
        
        // repat ladem
        $this->SetValue( "repeat", -1);

        // shuffle laden
        $this->SetValue( "shuffle", false);

        if ($band=="dab")
        {  
          // freq laden
          @$freq = $data->dab->freq;   
          if ($freq !=null) 
          {
              $this->SetValue( "freq", $freq);
          }
          else
          {
              $this->SetValue( "freq", -1);
          }
            
           // preset laden
          @$preset = $data->dab->preset;   
          if ($freq !=null) 
          {
              $this->SetValue( "preset", $preset);
          }
          else
          {
              $this->SetValue( "preset", -1);
          }

            // artist laden
            @$artist = $data->dab->service_label;    // artist laden
            if ($artist !=null) 
            {
                $this->SetValue( "artist", $artist);
            }
            else
            {
                $this->SetValue( "artist", "");
            }
            
            // album laden
                $this->SetValue( "album", "");
            
            // track laden
            @$track = $data->dab->dls;
            if ($track !=null) 
            {
                $this->SetValue( "track", $track);
            }
            else
            {
                $this->SetValue( "track", "");
            }
        }   

        else if ($band=="fm")
        {
         // freq laden
          @$freq = $data->fm->freq;   
          if ($freq !=null) 
          {
              $this->SetValue( "freq", $freq);
          }
          else
          {
              $this->SetValue( "freq", -1);
          }
           

            // preset laden
            @$preset = $data->fm->preset;   
            if ($freq !=null) 
            {
                $this->SetValue( "preset", $preset);
            }
            else
            {
                $this->SetValue( "preset", -1);
            }


            // artist laden
            @$artist = $data->rds->radio_text_a;    // artist laden
            if ($artist !=null) 
            {
                $this->SetValue( "artist", $artist);
            }
            else
            {
                $this->SetValue( "artist", "");
            }
            
            // album laden
                $this->SetValue( "album", "");
            
            // track laden
            @$track = $data->rds->radio_text_b;
            if ($track !=null) 
            {
                $this->SetValue( "track", $track);
            }
            else
            {
                $this->SetValue( "track", "");
            }
        }   
    
        else if ($band=="am")
        {
            // freq laden
            @$freq = $data->am->freq;   
            if ($freq !=null) 
            {
                $this->SetValue( "freq", $freq);
            }
            else
            {
                $this->SetValue( "freq", -1);
            }
                
            // preset laden
            @$preset = $data->am->preset;   
            if ($freq !=null) 
            {
                $this->SetValue( "preset", $preset);
            }
            else
            {
                $this->SetValue( "preset", -1);
            }

            // artist laden
            $this->SetValue( "artist", "");
            
            // album laden
            $this->SetValue( "album", "");
            
            // track laden
            $this->SetValue( "track", "");
        }

        else
        {
            // freq laden
            $this->SetValue( "freq", -1);

            // preset laden
            $this->SetValue( "preset", -1);
        
            // artist laden
            $this->SetValue( "artist", "");
            
            // album laden
            $this->SetValue( "album", "");
            
            // track laden
            $this->SetValue( "track", "");
        }
        $this->SetValue( "playtime", -1);
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetPower
@info                   Setzt ein Powermode in YAVR
@param                  power -> ein power mit einer int nummer versehen
                            siehe YAVR_{$this->InstanceID}_{$zone}_sound_power_list"
@date    				11.11.2023
*******************************************************************************/	
    public function SetPower(int $power)
    {
        $zone = $this->ReadPropertyString('var_zone');
        $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_power_list")["Associations"];
        if (@count($list) > 0) 
            {
                $power=$list[$power]["Name"];
                $this->SendDebug("setpower","setPower?power={$power}",0);
                @$this->RequestJSON("setPower?power={$power}");
                
                $this->GetStatus();
            }        
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetMute
@info                   Schaltet Mute ein oder aus
@param                  value -> (true oder false)
@date    				11.11.2023
*******************************************************************************/	
    public function SetMute(bool $value)
    {
        $text= "setMute?enable=";
        if ($value==true) $text =$text."true"; 
        else  $text =$text."false";
        $this->SendDebug("setmute",$text,0);
        @$this->RequestJSON($text);
        $this->GetStatus();
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetSoundProgram
@info                   Setzt ein Sondporgramm in  YAVR
@param                  programm -> ein sondprogrmm mit einer int nummer versehen
                            siehe YAVR_{$this->InstanceID}_{$zone}_sound_program_list"
@date    				11.11.2023
*******************************************************************************/	
    public function SetSoundProgram(int $programm)
    {
        $zone = $this->ReadPropertyString('var_zone');
        $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sound_program_list")["Associations"];
        if (@count($list) > 0) 
            {
                $programm=$list[$programm]["Name"];
                $this->SendDebug("setsoundprogram","setSoundProgram?program={$programm}",0);
                @$this->RequestJSON("setSoundProgram?program={$programm}");
                
                $this->GetStatus();
            }        
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetInput
@info                   Setzt den den Eingag von YAVR
@param                  programm -> ein sondprogrmm mit einer int nummer versehen
                            siehe "YAVR_{$this->InstanceID}_{$zone}_inputs"
@date    				11.11.2023
*******************************************************************************/	
    public function SetInput(int $input)
    {
        $zone = $this->ReadPropertyString('var_zone');
        $list =  $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_inputs")["Associations"];
        if (@count($list) > 0) 
        {
            $input=$list[$input]["Name"];
            $this->SetBuffer($this->InstanceID."_input", $input);
            $this->SendDebug("setInput","setInput?input={$input}",0);
            @$this->RequestJSON("setInput?input={$input}");
            $this->GetStatus();
        }
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetMute
@info                   Setzt die Lautstärke
@param                  volume -> (0-100)
@date    				11.11.2023
*******************************************************************************/	
    public function SetVolume(int $volume)
    {
        $volume =round(160/100*$volume,0);
        $this->SendDebug("setvolume","setVolume?volume={$volume}",0);
        @$this->RequestJSON("setVolume?volume={$volume}");
        $this->GetStatus();
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetPlayback
@info                   Setzt die Playback
@param                  volume -> (0-4)
@date    				11.11.2023
*******************************************************************************/	
    public function SetPlayback(int $Value)
    {
        if (  $this->GetBuffer($this->InstanceID."_input")== 'tuner')
        {
            switch ($Value) {
                case 0:
                    $this->SendDebug("setplayback tuner","switchPreset?dir=previous",0);
                    @$this->RequestJSONex("tuner","switchPreset?dir=previous");
                    break;
                case 1:
                    break;
                case 2:
                    break;
                case 3:
                    break;
                case 4:
                    $this->SendDebug("setplayback tuner","switchPreset?dir=next",0);
                    @$this->RequestJSONex("tuner","switchPreset?dir=next");
                    break;
                }
        }
        else  if (str_contains($this->GetBuffer($this->InstanceID."_input"), 'av'))
        {
            switch ($Value) {
                case 0:
                    $this->SendDebug("setplayback av","<Play_Control><Playback>Minus_1</Playback></Play_Control>",0);
                    @$this->RequestXML("<Play_Control><Playback>Minus_1</Playback></Play_Control>","PUT");
                    break;
                case 1:
                    $this->SendDebug("setplayback av","<Play_Control><Playback>Stop</Playback></Play_Control>",0);
                    @$this->RequestXML("<Play_Control><Playback>Stop</Playback></Play_Control>","PUT");
                    break;
                case 2:
                    $this->SendDebug("setplayback av","<Play_Control><Playback>Play</Playback></Play_Control>",0);
                    @$this->RequestXML("<Play_Control><Playback>Play</Playback></Play_Control>","PUT");
                    break;
                case 3:
                    $this->SendDebug("setplayback av","<Play_Control><Playback>Pause</Playback></Play_Control>",0);
                    @$this->RequestXML("<Play_Control><Playback>Pause</Playback></Play_Control>","PUT");
                    break;
                case 4:
                    $this->SendDebug("setplayback av","<Play_Control><Playback>Plus_1</Playback></Play_Control>",0);
                    @$this->RequestXML("<Play_Control><Playback>Plus_1</Playback></Play_Control>","PUT");
                    break;
                }
        }
       
        else
        {
            switch ($Value) {
                case 0:
                    $this->SendDebug("setplayback netusb","switchPreset?dir=previous",0);
                    @$this->RequestJSONex("netusb","setPlayback?playback=previous");
                    break;
                case 1:
                    $this->SendDebug("setplayback netusb","switchPreset?dir=stop",0);
                    @$this->RequestJSONex("netusb","setPlayback?playback=stop");
                    break;
                case 2:
                    $this->SendDebug("setplayback netusb","switchPreset?dir=play",0);
                    @$this->RequestJSONex("netusb","setPlayback?playback=play");
                    break;
                case 3:
                    $this->SendDebug("setplayback netusb","switchPreset?dir=pause",0);
                    @$this->RequestJSONex("netusb","setPlayback?playback=pause");
                    break;
                case 4:
                    $this->SendDebug("setplayback netusb","switchPreset?dir=next",0);
                    @$this->RequestJSONex("netusb","setPlayback?playback=next");
                    break;
                }
        }
        $this->GetStatus();
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetRepeat
@info                   Setzt die Playback
@param                  Value -> (0-2)
@date    				11.11.2023
*******************************************************************************/	
    public function SetRepeat(int $Value)
    {
        
        switch ($Value) {
            case 0:
                $this->SendDebug("setrepeat","setRepeat?mode=off",0);
                @$this->RequestJSONex("netusb","setRepeat?mode=off");
				break;
            case 1:
                $this->SendDebug("setrepeat","setRepeat?mode=all",0);
                @$this->RequestJSONex("netusb","setRepeat?mode=all");
                break;
            case 2:
                $this->SendDebug("setrepeat","setRepeat?mode=one",0);
                @$this->RequestJSONex("netusb","setRepeat?mode=one");
                break;
            }
        $this->GetStatus();
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetRepeat
@info                   Setzt die Suffle
@param                  volume -> true false
@date    				11.11.2023
*******************************************************************************/	
    public function SetShuffle(bool $Value)
    {
        $text= "setShuffle?mode=";
        if ($Value==true) $text =$text."on";
        else  $text =$text."off";
        $this->SendDebug("setshuffle",$text,0);
        @$this->RequestJSONex("netusb",$text);

        $this->GetStatus();
    }
  
/*******************************************************************************
@author					Black-Blade
@brief					SetSurround_AI
@info                   Schaltet die AI für Sound ein oder aus
@param                  volume -> (0-2)
@date    				11.11.2023
*******************************************************************************/	
    public function SetSurround_AI(bool $Value)
    {
        if ($Value==true)
        {  
            $this->SendDebug("setsurround_ai","<Surround><Program_Sel><Current><Surround_AI>On</Surround_AI></Current></Program_Sel></Surround>",0);
             @$this->RequestXML("<Surround><Program_Sel><Current><Surround_AI>On</Surround_AI></Current></Program_Sel></Surround>","PUT");
        }
        else 
        {
            $this->SendDebug("setsurround_ai","<Surround><Program_Sel><Current><Surround_AI>Off</Surround_AI></Current></Program_Sel></Surround>",0);
            @$this->RequestXML("<Surround><Program_Sel><Current><Surround_AI>Off</Surround_AI></Current></Program_Sel></Surround>","PUT");
        }
        $this->GetStatus();
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetSurround_AI
@info                   Schaltet den Surround Decoder 
@param                  Value -> je nach Liste
@date    				11.11.2023
*******************************************************************************/	
    public function SetSurr_Decoder(int $input)
    {
        $zone = $this->ReadPropertyString('var_zone');
        $list =  $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_surr_decoder_list")["Associations"];
        if (@count($list) > 0) 
        {
            $input=$list[$input]["Name"];
            $this->SendDebug("setsurr_decoder","setSurroundDecoderType?type={$input}",0);
            @$this->RequestJSON("setSurroundDecoderType?type={$input}");
            $this->GetStatus();
        }
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetCursor
@info                   Schaltet den SetCursor
@param                  input -> je nach Liste
@date    				11.11.2023
*******************************************************************************/	
    public function SetCursor(int $input)
    {
        $zone = $this->ReadPropertyString('var_zone');
        $list =  $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor")["Associations"];
        if (@count($list) > 0) 
        {
            $input=$list[$input]["Name"];
            $this->SendDebug("setcursor","<Cursor_Control><Cursor>{$input}</Cursor></Cursor_Control>",0);
            @$this->RequestXML("<Cursor_Control><Cursor>{$input}</Cursor></Cursor_Control>","PUT");
            $this->GetStatus();
        }
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetCursor_Control
@info                   Schaltet die SetCursor_Control
@param                  input -> je nach Liste
@date    				11.11.2023
*******************************************************************************/	
    public function SetCursor_Control(int $input)
    {
        $zone = $this->ReadPropertyString('var_zone');
        $list =  $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_cursor_control")["Associations"];
        if (@count($list) > 0) 
        {
            $input=$list[$input]["Name"];
            $this->SendDebug("setcursor_control","<Cursor_Control><Menu_Control>{$input}</Menu_Control></Cursor_Control>",0);
            @$this->RequestXML("<Cursor_Control><Menu_Control>{$input}</Menu_Control></Cursor_Control>","PUT");
            $this->GetStatus();
        }
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetPartyMode
@info                   Schaltet den Parymodus ein/aus
@param                  Value -> true false
@date    				11.11.2023
*******************************************************************************/	
    public function SetPartyMode(bool $Value)
    {
            $text="setPartyMode?enable=";
            if ($Value==true) $text =$text."true";
            else $text =$text."false";
            $this->SendDebug("setpartymode",$text,0);
            @$this->RequestJSONex("system",$text);
            $this->GetStatus();
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetSleep
@info                   Schaltet die SetSleep
@param                  input -> je nach Liste
@date    				29.08.2023
*******************************************************************************/	
    public function SetSleep(int $input)
    {
        $zone = $this->ReadPropertyString('var_zone');
        $list =  $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_sleep")["Associations"];
        if (@count($list) > 0) 
        {
            $input=$list[$input]["Name"];
            $this->SendDebug("setsleep","setSleep?sleep={$input}",0);
            @$this->RequestJSON("setSleep?sleep={$input}");
            $this->GetStatus();
        }
    }
    

/*******************************************************************************
@author					Black-Blade
@brief					SetRadioband
@info                   Schaltet das Radioband um
@param                  Value -> am fm dab 
@date    				11.11.2023
*******************************************************************************/	
    public function SetRadioband(int $Value)
    {    
        $zone = $this->ReadPropertyString('var_zone');
        $list =  $list =  IPS_GetVariableProfile("YAVR_{$this->InstanceID}_{$zone}_radioband")["Associations"];
        if (@count($list) > 0) 
        {
            $input=$list[$Value]["Name"];
            $this->SendDebug("tuner","setBand?band={$input}",0);
            @$this->RequestJSONex("tuner","setBand?band={$input}");
           $this->GetStatus();
        }
    }

/*******************************************************************************
@author					Black-Blade
@brief					SetPreset
@info                   Schaltet den Radiosender um
@param                  Value -> true false
@date    				11.11.2023
*******************************************************************************/	
    public function SetPreset(int $Value)
    {
        $zone = $this->ReadPropertyString('var_zone');
        $zoneMapper = array(
            'Main_Zone' => 'main',
            'Zone_2' => 'zone2',
            'Zone_3' => 'zone3',
            'Zone_4' => 'zone4'
        );

        $zone = $zoneMapper[$zone];

        $band = $this->GetBuffer($this->InstanceID."_radioband");
        $this->SendDebug("setPreset","recallPreset?zone={$zone}&band={$band}&num={$Value}",0);
     
        @$this->RequestJSONex("tuner","recallPreset?zone={$zone}&band={$band}&num={$Value}");
        $this->GetStatus();
    }

/*******************************************************************************
@author					Black-Blade
@brief					Enhancer
@info                   Setzt den Enhancer
@param                  Value -> true false
@date    				11.11.2023
*******************************************************************************/	
    public function SetEnhancer(bool $Value)
    {
        $text="setEnhancer?enable=";
        if ($Value==true) $text = $text."true";
        else $text =$text."false";
        $this->SendDebug("setenhancer",$text,0);
        @$this->RequestJSON($text,false);
        $this->GetStatus();
    }
}