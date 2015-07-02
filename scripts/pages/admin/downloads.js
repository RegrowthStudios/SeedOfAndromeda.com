
function addDownload(dlCountOverride) {
    if (typeof addDownload.dlCount == "undefined") {
        addDownload.dlCount = 1;
    }
    if (typeof dlCountOverride === "number") {
        addDownload = dlCountOverride;
    }
    addDownload.dlCount++;

    var html = '<label for="download-' + addDownload.dlCount + '">File:</label><input id="download-' + addDownload.dlCount + '" value="1" type="file" name="download-' + addDownload.dlCount + '" /><br/><br/>';
    html += '<div class="text"><div id="download-' + addDownload.dlCount + '-text" class="edittitle"><?php echo $download["download-' + addDownload.dlCount + '-text"];?></div></div>';
    if (addDownload.dlCount != 3) { html += '<br/><br/>'; }
    $("#add-download").before(html);

    if (addDownload.dlCount == 3) { $("#add-download").remove(); }

    $("input['download-number']").val(addDownload.dlCount);
    return;
}