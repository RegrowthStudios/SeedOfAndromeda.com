<!DOCTYPE html>
<!-- I don't know what I'm doing with php, so I'm hoping you guys will sort it out.
    
//Messy code incoming!
    
//this is just a skeleton style. dont want to mess it up completely :3
-->

    <div id="window" style="width:100%; height:550px; position:absolute; overflow:hidden; top:0px; left:0px;">
        <div style="height:50px; width:100%; position: absolute; background-color:#dddddd;">
            <div style="float:left;">Chan name</div><div style="float:left;">|</div> ([- for status) Buttons
        </div>
        <div id="msgs" style="height:450px; width:100%; padding:0px; position: absolute; top:50px; background-color:#ffffff;">
           
        </div>
        <div style="height:50px; width:100%; position: absolute; top:500px; background-color:#eeeeee;">
            <div style="height:50px; width:1px; position: absolute; background-color:#bbbbaa;" id="input">
                Type your shit. 
            </div>
            <div style="height:50px; width:80px; position: absolute; right:0px; background-color:#888888;">
                Send >> 
            </div>
        </div>
        <script>
            document.getElementById("input").style.width = (window.innerWidth - 80)+"px";
        </script>
    </div>
    
    <div style="visibility: hidden; overflow:hidden; height:0px; width:0px;">
        <!-- Templates for messenger -->
        <div id="mesgin">
            <div style="margin:3px;">
                <div style="width:80%; float:left;">
                    <div style="background-color:#eeeecc; float:left; padding:5px; min-width:100px; display: inline-block;">
                        $message.content
                        <div style="width:1px; height:9px;"></div>
                    </div><br/><br/>
                    <div style="float:left; width:1px; height:5px; position:relative;">
                        <div style="font-size:9px; position: absolute; top:-14px; left: 5px;">
                            $message.time.computed
                        </div>
                        <div style="font-size:9px; position: absolute; top:-14px; left: 45px; width:200px;">
                            <div style="float:left;">
                                $message.user
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div id="mesgout" style="">
            <div style="margin:3px;">
                <div style="width:80%; float:right;">
                    <div style="background-color:#eeeecc; float:right; padding:5px; min-width:100px; display: inline-block;">
                        $message.content
                        <div style="width:1px; height:9px;"></div>
                    </div><br/><br/>
                    <div style="float:right; width:1px; height:5px; position:relative;">
                        <div style="font-size:9px; position: absolute; top:-14px; right: 5px;">
                            $message.time.computed
                        </div>
                        <div style="font-size:9px; position: absolute; top:-14px; right: 45px; width:200px;">
                            <div style="float:right;">
                                You
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="justNowTimespan">60</div>
        <div id="messageTimeFormat">$message.time.HH:$message.time.MM:$message.time.SS</div>
        <div id="ownMessageName">You</div><!-- or $message.user -->
    </div>
    
    <script>
        function computeTime(time){
            time = new Date(time *1000);
            return document.getElementById('messageTimeFormat').innerHTML.replace("$message.time.HH",function(){if(time.getHours() < 10){return "0"+time.getHours();}else{return time.getHours();} }).replace("$message.time.MM",function(){if(time.getMinutes() < 10){return "0"+time.getMinutes();}else{return time.getMinutes();} }).replace("$message.time.SS",function(){if(time.getSeconds() < 10){return "0"+time.getSeconds();}else{return time.getSeconds();} });
        }
    
        function msgin(sender,message,time){
            var template = document.getElementById('mesgin').innerHTML;
            template = template.replace("$message.user",sender);
            template = template.replace("$message.time.computed",computeTime(time));
            template = template.replace("$message.content",message);
            document.getElementById("msgs").innerHTML+=template;
        }
        
        function msgout(sender,message,time){
            var template = document.getElementById('mesgout').innerHTML;
            template = template.replace("$message.user",document.getElementById("ownMessageName").innerHTML);
            template = template.replace("$message.user",sender);
            template = template.replace("$message.time.computed",computeTime(time));
            template = template.replace("$message.content",message);
            document.getElementById("msgs").innerHTML+=template;
        }
    
        msgin("thejeremail","hi dude",74594356);
        msgout("DummyUser","swagfsadfasdejsaeffy fyweugdf wegrf qwyegf wqyegf oqywegf qyuwgefqwrigufdsrtgnbh aerugfuqiefieirtuh wadfygaipryg wergiwerjh blah ehfbywrgf aghefiuwagefiugaweg asefrerwegr wer qweygrqyug4r5qyug4rg q45r7g wegr gw rfgq weurgfqwe grwger 9khfgweirgoawurygfger",74594356);
        msgin("thejeremail","haha, sir spamalot xD",74594356);
        msgout("DummyUser","hehe, lol",234234534);
    </script>