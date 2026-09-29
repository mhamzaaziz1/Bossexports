"use strict";

$(document).ready(function(){

    	var editfile = $('#bpmn_content').val();

      var diagramUrl = '../bpmndiagram/'+'diagram'+editfile;
      //var diagramUrl = site_url+'modules/bmpn/bpmndiagram/'+'diagram'+editfile; 

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

         

            $('textarea#bpmn_content').html(result.xml);

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

      $.get(diagramUrl, openDiagram, 'text');

  });