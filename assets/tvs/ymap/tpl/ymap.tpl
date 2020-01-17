[+js+]
<input style="display:none;" id="tv[+tv_id+]" name="tv[+tv_id+]" value="[+tv_value+]">
<div id="ymap[+tv_id+]" style="width:[+width+];height:[+height+];"></div>
<script type="text/javascript">
(function($) {
    $('#ymap[+tv_id+]').ymapTV({
        coords:'[+tv_value+]',
        tv:'#tv[+tv_id+]',
        zoom: [+zoom+],
        noKey: [+noKey+]
    });
})(jQuery)
</script>
