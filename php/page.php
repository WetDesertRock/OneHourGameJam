<?php
$templateBasePath = "template/";

$pageTitles = Array(
	"main" => "Main Page",
	"login" => "Login",
	"submit" => "Submit Game",
	"newjam" => "Schedule New Jam",
	"assets" => "Assets",
	"editasset" => "Edit Asset",
	"rules" => "Rules",
	"config" => "Configuration",
	"editcontent" => "Manage Content",
	"editjam" => "Edit Jam",
	"editentry" => "Edit Entry",
	"editusers" => "Manage Users",
	"edituser" => "Edit User",
	"themes" => "Theme Voting",
	"usersettings" => "User Settings",
	"entries" => "Entries",
	"jam" => "Jam",
	"jams" => "Jams",
	"author" => "Author",
	"authors" => "Authors",
	"privacy" => "Privacy",
    "userdata" => "User Data",
    "adminlog" => "Admin Log"
);

function RenderPageSpecific($page, &$config, &$users, &$games, &$jams, &$satisfaction, &$loggedInUser, &$assets, &$cookies, &$adminVotes, &$loggedInUserAdminVotes, &$nextSuggestedJamDateTime){
    global $_GET, $templateBasePath, $pageTitles;
	AddActionLog("RenderPageSpecific");
	StartTimer("RenderPageSpecific");

    $render = Array();
    
    //$render["CURRENT_TIME"] = gmdate("d M Y H:i", time());
    $render["page_title"] = $pageTitles[$page];
    $render["template_path"] = $templateBasePath;

    //Special processing for specific pages
    switch($page){
        case "edituser":
            if(IsAdmin($loggedInUser) !== false){
                $editingUsername = $_GET["username"];
                $editingUsername = trim(strtolower($editingUsername));
                if(!isset($users[$editingUsername])){
                    die("no user selected");
                }
                $render["editinguser"] = $users[$editingUsername];
                if($users[$editingUsername]["admin"] != 0){
                    $render["editinguser"]["is_admin"] = 1;
                }
            }
        break;
        case "editjam":
            if(IsAdmin($loggedInUser) !== false){
                $jamID = intval($_GET["jam_id"]);
                $jamFound = false;
                foreach($jams as $i => $jam){
                    if(intval($jam["jam_id"]) == $jamID){
                        $render["editingjam"] = RenderJam($config, $users, $games, $jam, $jams, $satisfaction, $loggedInUser, 0);
                        $jamFound = true;
                        break;
                    }
                }
                if(!$jamFound){
                    die("no jam selected");
                }
                $editingJamDate = date("Y-m-d", strtotime($render["editingjam"]["date"]));
                $render["editingjam"]["html_startdate"] = $editingJamDate;
            }
        break;
        case "editasset":
            if(IsAdmin($loggedInUser) !== false){
                if(isset($_GET["asset_id"])){
                    $assetID = intval($_GET["asset_id"]);
                    $render["editingasset"] = ((isset($assets[$assetID])) ? $assets[$assetID] : Array());
                }
            }
        break;
        case "editentry":
            if(IsAdmin($loggedInUser) !== false){
                $entryID = intval($_GET["entry_id"]);
                $render["editingentry"] = Array();
                foreach($games as $i => $game){
                    if($game["id"] == $entryID){
                        $render["editingentry"] = RenderGame($users, $game, $jams);
                        break;
                    }
                }
                if(count($render["editingentry"]) == 0){
                    die("no entry selected");
                }
            }
        break;
        case "jam":
            $viewingJamNumber = ((isset($_GET["jam"])) ? intval($_GET["jam"]) : 0);
            if($viewingJamNumber == 0){
                die("invalid jam number");
            }

            $pass = FALSE;
            foreach($jams as $i => $jam){
                if($jam["jam_number"] != $viewingJamNumber){
                    continue;
                }

                if($jam["jam_deleted"] == 1){
                    continue;
                }

                $render["viewing_jam"] = RenderJam($config, $users, $games, $jam, $jams, $satisfaction, $loggedInUser, 0);
                $pass = TRUE;
                break;
            }

            if($pass == FALSE){
                die("jam does not exist");
            }

            $render["page_title"] = "Jam #" . $viewingJamNumber . ": ".$render["viewing_jam"]["theme"];
        break;
        case "author":
            $viewingAuthor = ((isset($_GET["author"])) ? ("".$_GET["author"]) : "");
            if($viewingAuthor == ""){
                die("invalid author name");
            }

            $render['show_edit_link'] = $viewingAuthor == $loggedInUser["id"];
            $render["author_bio"] = LoadBio($viewingAuthor);
            $render["viewing_author"] = RenderUser($config, $cookies, $users[$viewingAuthor], $users, $games, $jams, $adminVotes, $loggedInUserAdminVotes);
            $render["page_title"] = $viewingAuthor;
        break;
        case "submit":
            $currentJamData = GetCurrentJamNumberAndID();
            if($currentJamData["NUMBER"] == 0){
                die("no jam to submit to");
            }
            $jamNumber = (isset($_GET["jam_number"])) ? intval($_GET["jam_number"]) : $currentJamData["NUMBER"];
            $jam = GetJamByNumber($jams, $jamNumber);
            if (!$jam) {
                die('jam not found');
            }

            $render["submit_jam"] = RenderSubmitJam($config, $users, $games, $jam, $jams, $satisfaction, $loggedInUser);
            $colorNumber = rand(0, count($jam["colors"]) - 1);
            $render["user_entry_color"] = $jam["colors"][$colorNumber];

            foreach($games as $i => $game){
                if($game["author"] != $loggedInUser["username"]){
                    continue;
                }

                if($game["jam_number"] != $jamNumber){
                    continue;
                }

                if($game["entry_deleted"] == 1){
                    continue;
                }

                //Determine entry color number
                foreach($jam["colors"] as $colorIndex => $color){
                    if($color == $game["color"]){
                        $colorNumber = $colorIndex;
                        break;
                    }
                }

                $render["user_entry_color_number"] = $colorNumber;
                $render["user_entry_color"] = $jam["colors"][$colorNumber];

                $render["user_submitted_to_this_jam"] = true;
                $render["user_entry_name"] = $game["title"];
                if($game["screenshot_url"] != "logo.png"){
                    $render["user_entry_screenshot"] = $game["screenshot_url"];
                }
                $render["user_entry_url"] = $game["url"];
                $render["user_entry_url_web"] = $game["url_web"];
                $render["user_entry_url_windows"] = $game["url_windows"];
                $render["user_entry_url_mac"] = $game["url_mac"];
                $render["user_entry_url_linux"] = $game["url_linux"];
                $render["user_entry_url_ios"] = $game["url_ios"];
                $render["user_entry_url_android"] = $game["url_android"];
                $render["user_entry_url_source"] = $game["url_source"];
                $render["user_entry_desc"] = $game["description"];
                //$dictionary["user_entry_color"] = $game["color"];
                //$dictionary["user_entry_color_number"] = $game["color_number"];

                if(isset($game["has_url"])){$render["user_has_url"] = 1;}
                if(isset($game["has_url_web"])){$render["user_has_url_web"] = 1;}
                if(isset($game["has_url_windows"])){$render["user_has_url_windows"] = 1;}
                if(isset($game["has_url_mac"])){$render["user_has_url_mac"] = 1;}
                if(isset($game["has_url_linux"])){$render["user_has_url_linux"] = 1;}
                if(isset($game["has_url_ios"])){$render["user_has_url_ios"] = 1;}
                if(isset($game["has_url_android"])){$render["user_has_url_android"] = 1;}
                if(isset($game["has_url_source"])){$render["user_has_url_source"] = 1;}
                break;
            }

            if (!isset($render["user_submitted_to_this_jam"]) && $jamNumber != $currentJamData["NUMBER"]) {
                die('Cannot make a new submission to a past jam');
            }
        break;
        case "userdata":
            $render["userdata_assets"] = GetAssetsOfUserFormatted($loggedInUser["username"]);
            $render["userdata_entries"] = GetEntriesOfUserFormatted($loggedInUser["username"]);
            $render["userdata_poll_votes"] = GetPollVotesOfUserFormatted($loggedInUser["username"]);
            $render["userdata_themes"] = GetThemesOfUserFormatted($loggedInUser["username"]);
            $render["userdata_theme_votes"] = GetThemeVotesOfUserFormatted($loggedInUser["username"]);
            $render["userdata_users"] = GetUsersOfUserFormatted($loggedInUser["username"]);
            $render["userdata_jams"] = GetJamsOfUserFormatted($loggedInUser["username"]);
            $render["userdata_satisfaction"] = GetSatisfactionVotesOfUserFormatted($loggedInUser["username"]);
            $render["userdata_sessions"] = GetSessionsOfUserFormatted($loggedInUser["id"]);
            $render["userdata_adminlog_admin"] = GetAdminLogForAdminFormatted($loggedInUser["username"]);
            $render["userdata_adminlog_subject"] = GetAdminLogForSubjectFormatted($loggedInUser["username"]);
            $render["userdata_admin_vote_voter"] = GetAdminVotesCastByUserFormatted($loggedInUser["username"]);
            $render["userdata_admin_vote_subject"] = GetAdminVotesForSubjectUserFormatted($loggedInUser["username"]);
        break;
        case "newjam":
            $render["next_jam_suggested_date"] = gmdate("Y-m-d", $nextSuggestedJamDateTime);
            $render["next_jam_suggested_time"] = gmdate("H:i", $nextSuggestedJamDateTime);
        break;
        case "usersettings":
            $render["user_bio"] = LoadBio($loggedInUser["username"]);
        break;
    }

    StopTimer("RenderPageSpecific");
    return $render;
}


?>