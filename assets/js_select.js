//
// * Javascript
// *
// * @package    ajaxdemo
// * Developer: 2020 Ricoshae Pty Ltd (http://ricoshae.com.au)
//

require(['core/first', 'jquery', 'jqueryui', 'core/ajax'], function(core, $, bootstrap, ajax) {

  // -----------------------------
  $(document).ready(function() {

    //  toggle event
    $('#id_stickycolid').change(function() {
      // get current value then call ajax to get new data
      var selectedstickycolid = $('#id_stickycolid').val();
      ajax.call([{
        methodname: 'mod_stickynotes_get_notes_column_select',
        args: {
          'id': selectedstickycolid
        },
      }])[0].done(function(response) {
        // clear out old values
        $('#id_selectorder').html('');
        var data = JSON.parse(response);
        for (var i = 0; i < data.length; i++) {
          $('<option/>').val(data[i].ordernote).html(data[i].message).appendTo('#id_selectorder');
        }
        setnewvalue();
        return;
      }).fail(function(err) {
        console.log(err);
        //notification.exception(new Error('Failed to load data'));
        return;
      });

    });

    $('#id_selectorder').change(function() {
      setnewvalue();
    });

    function setnewvalue() {
      console.log($('#id_selectorder').val());
      $('input[name = ordernote]').val($('#id_selectorder ').val());
    }

  });
});