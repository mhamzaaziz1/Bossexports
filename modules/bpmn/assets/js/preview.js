$j( document ).ready(function() {

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

$j(function() {

       var editfile = '';

        editfile = $j('textarea#bpmn_content').val();

        var adminurl = admin_url;

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

          // var overlays = bpmnModeler.get('overlays');

          // overlays.add('SCAN_OK', 'note', {

          //   position: {

          //     bottom: 0,

          //     right: 0

          //   },

          //   html: '<div class="diagram-note">Mixed up the labels?</div>'

          // });



          // add marker

          // canvas.addMarker('SCAN_OK', 'needs-discussion');

        } catch (err) {



          console.error('could not import BPMN 2.0 diagram', err);

        }

      }





      // load external diagram file via AJAX and open it

      $j.get(diagramUrl, openDiagram, 'text');



});



$j('#print').on('click',function(){

window.print();

 });

});