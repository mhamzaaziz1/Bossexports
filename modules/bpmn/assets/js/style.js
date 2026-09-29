$j( document ).ready(function() {
  // Code that uses jQuery's $j can follow here.

  $j(document).off('keypress.shortcuts keydown.shortcuts keyup.shortcuts');
  $j('#expand-button').on('click',function(){
  $j('#top-panel').slideToggle( "slow" );
  $j('#expand-button').hide();
  $j("html, body").animate({ scrollTop: 0 }, "slow");
  return false;
 });
 $j('#close').on('click',function(){
  $j('#top-panel').slideToggle( "slow" );
  $j('#expand-button').show();
 });

function validate_bpmn_grup_form(){
    appValidateForm($j('#bpmn-group-form'), {
        name: 'required',
        description : 'required',
    });
  }
 function new_bpmn(){
        $j('#bpmn_create').modal('show');
  }
$j("button.bpmn-group-btn").on('click', function (e) {
      var count=0;
      var data = $j('#bpmn-group-form').serializeArray().reduce(function(obj, item) {
          
          if(item.value=='')
          {
            validate_bpmn_grup_form();
            count++;
          }   
      }, {});

      if(count>0)
      {
        alert('please fill all fields');
      }
      else
      {
     $j.ajax({ 
          url: admin_url+'bpmn/group',
          type: 'POST',
          data:$j('#bpmn-group-form').serialize(),  
          success: function (result) {
             var response = JSON.parse(result);
              var category = $j('#inputGroupSelect04');
          category.find('option:first').after('<option value="'+response.id+'" selected>'+response.name+'</option>');
          $j('#smessage').text('Group added successfully!');
          $j("#bpmn-group-form")[0].reset();
          setTimeout(function(){ $j('#myModal').hide();}, 2000);
          }
      });
    }
    });
       var editfile = '';
        editfile = $j('textarea#bpmn_content').val();
        console.log(editfile);

        
        adminurl = admin_url.substring(0, admin_url.lastIndexOf("/"));
        adminurl = adminurl.substring(0, adminurl.lastIndexOf("/"));
        if(editfile=='')
        {
             var globalxml = adminurl+'/modules/bpmn/bpmndiagram/diagramcommon.bpmn';
        }
        else
        {
            var globalxml = adminurl+'/modules/bpmn/bpmndiagram/'+'diagram'+editfile;
        }
      var diagramUrl = globalxml;

      // modeler instance
      var bpmnModeler = new BpmnJS({
        container: '#canvas',
        keyboard: {
          bindTo: window
        }
        
      });

      /**
       * Save diagram contents and print them to the console.
       */
      async function exportDiagram() {

        try {

          var result = await bpmnModeler.saveXML({ format: true });
         
            $j('textarea#bpmn_content').html(result.xml);
             console.log(result.xml);
        } catch (err) {

          console.error('could not save BPMN 2.0 diagram', err);
        }
      }

      /**
       * Open diagram in our modeler instance.
       *
       * @param {String} bpmnXML diagram to display
       */
      async function openDiagram(bpmnXML) {

        // import diagram
        try {

          await bpmnModeler.importXML(bpmnXML);

          // access modeler components
          var canvas = bpmnModeler.get('canvas');
          var overlays = bpmnModeler.get('overlays');

          // zoom to fit full viewport
          canvas.zoom('fit-viewport');

          // attach an overlay to a node
          overlays.add('SCAN_OK', 'note', {
            position: {
              bottom: 0,
              right: 0
            },
            html: '<div class="diagram-note">Mixed up the labels?</div>'
          });

          // add marker
          canvas.addMarker('SCAN_OK', 'needs-discussion');
        } catch (err) {

          console.error('could not import BPMN 2.0 diagram', err);
        }
      }


      // load external diagram file via AJAX and open it
      $j.get(diagramUrl, openDiagram, 'text');

      // wire save button

    $j("button.bpmn-btns").on('click', function (e) {

         var count=0;
      var data = $j('#bpmn-form').serializeArray().reduce(function(obj, item) {
          
          if(item.value=='')
          {
            validate_bpmn_grup_form();
            count++;
          }   
      }, {});

      if(count>0)
      {
        $j('#top-panel').slideToggle( "slow" );
        $j('#expand-button').hide();
      }
        exportDiagram();
        setTimeout( function(){ 
    // Do something after 1 second 
        $j('#bpmn-form').submit();
      }  , 500 );
    })
//     validate_bpmn_form();
//     function validate_bpmn_form(){
//     var response = appValidateForm($j('#bpmn-form'), {
//         title: 'required',
//         description : 'required',
//         bpmn_group_id:'required',
//     });
// }


});

