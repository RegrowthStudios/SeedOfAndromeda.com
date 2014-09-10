<!DOCTYPE html>
<!-- I don't know what I'm doing with php, so I'm hoping you guys will sort it out.
    
//Messy code incoming!
    
//this is just a skeleton style. dont want to mess it up completely :3
-->

    <div id="window" style="width:100%; height:550px; position:absolute; top:0px; left:0px;">
        <div style="height:50px; width:100%; position: absolute; background-color:#dddddd;">
            Buttons and shit
        </div>
        <div style="height:450px; width:100%; position: absolute; top:50px; background-color:#ffffff;">
            Other people's shit! This shit is unstyled, because jeremy can't make shit look nice.
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