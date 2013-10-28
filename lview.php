<?php
/*
  libvirt view beta

  A quickly hacked together dashboard to view resource utilization
  on libvirt/KVM hypervisors, tested with php-libvirt-0.4.4-1
  Requires TLS across hypervisors - change $l_hosts to reflect active hypervisors

  JB Gericke
  03/11/2011


*/

   function l_conn_init($l_hosts) {

	foreach($l_hosts as $conn_host) {
	
	  if($conn = libvirt_connect('qemu://'.$conn_host.'/system', true)) {
 
            v_conn_display($conn);
 
          } else {

            printf('Error: Cannot connect to URI: ' . $conn_host);

          }

     	}

        return(0);

   }

   function v_conn_display($conn) {

        $dom_vms_active = 0;
        $dom_total_memused = 0;
        printf('<p>'."\n".'<div class="container">'."\n");

        if($conn_hostname = libvirt_connect_get_hostname($conn)) {

          printf('<div class="hypervisor">'.
                 '<div class="hyp_title">'. 
                 $conn_hostname. 
                 '</div>'
          );

          if($conn_info = libvirt_node_get_info($conn)) {

            printf('<div class="hyp_details">'.
                   '<u>Model:</u> '.
                   $conn_info["model"].
                   ' <u>Memory (MB):</u> '.
                   round($conn_info["memory"]/1024).
                   ' <u>CPUs:</u> '.
                   $conn_info["cpus"].
                   ' <u>Nodes:</u> '.
                   $conn_info["nodes"].
                   ' <u>Sockets:</u> '.
                   $conn_info["sockets"].
                   ' <u>Cores:</u> '.
                   $conn_info["cores"].
                   ' <u>Threads:</u> '.
                   $conn_info["threads"].
                   ' <u>MHz:</u> '.
                   $conn_info["mhz"].
                   '</div>'.
                   '</div>'
            ); 

            if($doms = libvirt_list_domains($conn)) {

              foreach($doms as $domain) {

                     if($dom_res = libvirt_domain_lookup_by_name($conn, $domain)) {

                       if($dom_info = libvirt_domain_get_info($dom_res)) {

                         if($dom_info["state"] == 1 || $dom_info["state"] == 2 | $dom_info["state"] == 3) {

                           $dom_total_memused += round($dom_info["memory"]/1024);
                           $dom_vms_active++;

                           printf('<div class="domain">'.
                                  '<div class="dom_title">'.
                                  'Active VM: '.
                                  $domain.
                                  '</div>'.
                                  '<div class="dom_details">'.
                                  '<u>Memory:</u> '.
                                  round($dom_info["memory"]/1024).
                                  ' <u>State:</u> '.
                                  l_dom_getstate($dom_info["state"]).
                                  ' <u>vCPUs:</u> '.
                                  $dom_info["nrVirtCpu"].
                                  '</div>'.
                                  '</div>'
                           );         

                         }

                       } else {

                         printf('Error: Unable to retrieve information for domain ' . $domain);

                       }

                     } else {

                       printf('Error: Unable to lookup domain: ' . $domain);

                     }

              }

            } else {

              printf('Error: Failed to list domains');

            }

            $dom_total_memperc = round(($dom_total_memused*100)/round($conn_info["memory"]/1024));
            $dom_available_mem = round($conn_info["memory"]/1024)-$dom_total_memused;

            printf('<div class="mem_info">'.
                   '<div class="mem_avail">'.
                   '<div class="mem_used" style="width:'. 
                   $dom_total_memperc. 
                   'px;">'.
                   '</div>'.
                   '</div>'.
                   '<div class="mem_details">'.
                   '<u>Active VMs:</u> '.
                   $dom_vms_active.
                   ' <u>Available Mem (MB):</u> '.
                   $dom_available_mem.
                   ' <u>Total Memory Allocated (MB)</u> '.
                   $dom_total_memused.
                   ' <u>Memory Used (%%):</u> '.
                   $dom_total_memperc.
                   '</div>'.
                   '</div>'
            );

          } else {
         
            print('</div>Error: Unable to retrieve hypervisor information');

          }

        } else {

          print('Error: Unable to retrieve hypervisor hostname');

        }

        printf("\n".'</div>'."\n".'</p>'."\n");
        
   }

   function l_dom_getstate($dom_sp_state) {

        switch($dom_sp_state) {

          case 0: $dom_sp_state_dec = "None";
                  break;
          case 1: $dom_sp_state_dec = "Running";
                  break;
          case 2: $dom_sp_state_dec = "Blocked";
                  break;
          case 3: $dom_sp_state_dec = "Paused";
                  break;
          case 4: $dom_sp_state_dec = "Shutting Down";
                  break;
          case 5: $dom_sp_state_dec = "Shutdown";
                  break;
          case 6: $dom_sp_state_dec = "Crashed";
                  break;
          default: $dom_sp_state_dec = "Unknown";

        }

        return($dom_sp_state_dec);

   }

   function l_view_img($image_name) {

        $image = array();

        $image['hicon'] =
		'/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQ'.
                'gKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
                'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMj'.
                'IyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
                'MjIyMjIyMjL/wAARCAAgACADASIAAhEBAxEB/8QAHwAAAQUBAQ'.
                'EBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUF'.
                'BAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0f'.
                'AkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVW'.
                'V1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmq'.
                'KjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi'.
                '4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAA'.
                'AAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAEC'.
                'AxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNO'.
                'El8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVm'.
                'Z2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqK'.
                'mqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq'.
                '8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD13xL4xsvDgaJoLi7vBE'.
                'JRBCh+6cgEt0AJUjjJ46GvKNW8W+LfFm+Myf2PpzceXFkOyn1P'.
                'XI5HPynripfjPqt5pPi6K4tJdjLpQYqRkNiSTGfzrFttfDi2+2'.
                'QhPtLlI3h55DbfmXt+B70Aauk+LfFvhPZGJP7Y05ePLlyXVR6d'.
                '89Bx8o64r1fw14vtPEaIi21zaXRjMhhmQ8qMAkN0OCwGDg89BX'.
                'mdq9soM0u0RqMl5SAAPU9vzrr/AAjdW974gs7i1njngewuNskT'.
                'BlOJIRwRQB578ev+RkX/ALBH/tSSufXTrh7XTb1NphWcHbv+4P'.
                'N2k4xnk4PU9ewr3bxh8PtH8ZL5l6JI7tYvKSdD/Dz8pXuMsemD'.
                '7155q/gvWdAg8va0tgrKTLAplQAMGJK/ejJxk9VGeSaAF0Oxtr'.
                'ywnvLi1c3MV/HFbXU+GtrcLscllz1+8SSOmBkZ56bwP83iVphI'.
                'JUnS/mSUDAkVriIhx7N94exFc7oPg/UdXBeO2mSykcSk3sji3L'.
                '4A3rD/ABnAHOADjhhXp+jeG4NJlFy88tzeeWY/Nf5VVSQSqoOA'.
                'MqOuTx1q3O8VFIR//9k='.
                '';        

        $image['dicon'] =
 		'/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQ'.
		'gKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAx'.
		'NDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMj'.
		'IyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIy'.
		'MjIyMjIyMjL/wAARCAAYABgDASIAAhEBAxEB/8QAHwAAAQUBAQ'.
		'EBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUF'.
		'BAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0f'.
		'AkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVW'.
		'V1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmq'.
		'KjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi'.
		'4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAA'.
		'AAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAEC'.
		'AxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNO'.
		'El8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVm'.
		'Z2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqK'.
		'mqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq'.
		'8vP09fb3+Pn6/9oADAMBAAIRAxEAPwDpdb8UXsE01rpNnHJNE+'.
		'x5JnwFOOu0c49/0Ncx9o8SCf7aNdk+1Y/1W39z9NvTPvj8M1g+'.
		'KnZPiGdjMu6eQNtbaSPLJxmkuvEGp2mkSTWircXCy7EDrk7Qm4'.
		'9OScAnmk2krsTaSuz03Qte1O6EceqacsTO+xZoX+VjjIO08446'.
		'/oKK82+Huq3OqeNXm+33VzavuI85+Bjy9vyjgEbn6DuaKUJcyu'.
		'iYTU48yOx1/wABz3fiBNbsrnfIkhk8hgBztI4Pfr0OOnWsa40r'.
		'Ubm9jjSGW3vY5RINsBbJwVJIOBg5Izkj3NFFOUVJOMtmOcYzi4'.
		'yV0zpvC3gyLQhvSKOBnk82U8NJI2DjJGFUDJ+VRj3oooppWGlb'.
		'RH//2Q=='.
		'';
      
        header("Content-type: image/jpg");
        header("Content-length: 1064");

        echo base64_decode($image[$image_name]);
   }

 
   if(array_key_exists("image", $_REQUEST) && $_REQUEST["image"]=='hicon') 
     l_view_img('hicon'); 
   if(array_key_exists("image", $_REQUEST) && $_REQUEST["image"]=='dicon')
     l_view_img('dicon');



?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>LView Beta</title>
<style type="text/css">
		<!--
		@charset "utf-8";
		body {
        		padding: 0;
        		margin: 0;
        		font: 0.7em Tahoma, sans-serif;
        		line-height: 1.5em;
      			background: #669933;
        		color: #454545;
		}
                .header {
                        padding: 5px;
                        font: bold 1.4em Tahoma, sans-serif;
                        background: #E0E0E0;
                        color: #404040;
                        height: 20px;
                        border-style: solid;
                        border-color: #282828;
                        border-width: 0px 0px 4px 0px;
                }
		.content {
        		margin: 0 auto;
        		padding: 10px 0px 20px 0px;
                        width: 700px;
		}
                .container {
                        margin: 0 auto;
                        background: #666;
                        padding: 10px 0px 10px 0px;
                        border-style: solid;
                        border-width: 2px;
                        border-color: #282828;
			-webkit-border-radius: 15px;
                        -moz-border-radius: 15px;
                        border-radius: 15px;
                }
		.hypervisor {
        		margin: 0px;
                        padding: 15px 0px 15px 30px;
                        background: #FFF;
		}
		.hyp_title {
                        text-align: left;
                        padding-left: 38px;
        		font: bold 1.6em Tahoma, Arial, Sans-Serif;
        		color: #404040;
                        height: 32px;
                        background-image:url(<?php print(htmlentities($_SERVER['PHP_SELF']).'?image=hicon'); ?>);
                        background-repeat:no-repeat;
                        background-position: left top;
		}
		.hyp_details {
                        font: 1.1em Tahoma, Arial, Sans-Serif;
                        color: #404040;
                        background: #FFF;
		}
		.domain {
        		margin-left: 0px;
       			padding: 15px 0px 15px 70px;
                        background: #D0D0D0;
		}
		.dom_title {
                       text-align: left;
                       padding-left: 26px;
                       font: bold 1.2em Tahoma, Arial, Sans-Serif;
                       color: #202020;
                       height: 24px;
                       background-image:url(<?php print(htmlentities($_SERVER['PHP_SELF']).'?image=dicon'); ?>);
                       background-repeat:no-repeat;
                       background-position: left top;
		}
		.dom_details {
        	       font: 1.1em Tahoma, Arial, Sans-Serif;
                       color: #686868;
		}
		.mem_info {
        		margin: 0px;
       		 	padding: 15px 0px 15px 30px;
                        background: #E0E0E0;
		}
		.mem_details {
        		font: 1.1em Tahoma, Arial, Sans-Serif;
                        color: #505050;
		}
		.mem_avail {
        		background:#00ff00;
		        border:1px solid #000000;
		        width: 100px;
		        height: 15px;
		}
		.mem_used {
		        background:#ff0000;
		        border:none;
		        height: 15px;
		}

		-->
		</style>
</head>
<body>

<div class="header">LView Beta</div>
<div class="content">
   <?php

     /* Change the below to reflect relevant hypervisors
        - TLS/cert configuration across libvirt hosts is
          required for libvirt_connect */
     $l_hosts = array(
                       "node01.example.com", 
                       "node02.example.com", 
                       "node04.example.com", 
                       "node05.example.com", 
                       "node06.example.com"
     );

     l_conn_init($l_hosts);
    
   ?>
</div>

</body>
</html>


