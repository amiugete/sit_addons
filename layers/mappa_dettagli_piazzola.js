function puntoNelBuffer(latlng, feature) {

        const geometry = feature.geometry;

        if (!geometry) {
            return false;
        }

        const punto = [latlng.lng, latlng.lat];

        if (geometry.type === "Polygon") {
            return puntoNelPoligono(
                punto,
                geometry.coordinates
            );
        }

        if (geometry.type === "MultiPolygon") {
            return geometry.coordinates.some(
                polygon => puntoNelPoligono(
                    punto,
                    polygon
                )
            );
        }

        return false;
      }

      function puntoNelPoligono(punto, poligono) {

          const x = punto[0];
          const y = punto[1];

          let dentro = false;

          const anello = poligono[0];

          for (
              let i = 0, j = anello.length - 1;
              i < anello.length;
              j = i++
          ) {

              const xi = anello[i][0];
              const yi = anello[i][1];

              const xj = anello[j][0];
              const yj = anello[j][1];

              const interseca =
                  ((yi > y) !== (yj > y)) &&
                  (x < (xj - xi) * (y - yi) / (yj - yi) + xi);

              if (interseca) {
                  dentro = !dentro;
              }
          }

          return dentro;
      }

      mappaPiazzola = creaMappa("mappa_piazzola");

      mappiazzola = mappaPiazzola.map;
      initialView = mappaPiazzola.initialView;

      modalitaModifica = false;
      markerPiazzola = null;
      posizioneModificata = false;
      posizioneOriginale = null;

      //layer piazzola
      layerPiazzola = L.geoJSON(null, {
        pointToLayer: function(feature, latlng) {
          markerD = L.marker(latlng, {
              icon: creaIconaPiazzola(feature.properties),
              draggable: false
          });
          markerPiazzola=markerD;
          markerD.feature = feature;
          piazzoleById.set(feature.properties.id_piazzola, markerD);

          // posizione iniziale
          markerD.posizionePrecedente = latlng;

          markerD.on('dragend', function() {

            nuovaPosizione = markerD.getLatLng();

            if (!bufferFeature) {
                return;
            }

            dentro = puntoNelBuffer(
                nuovaPosizione,
                bufferFeature
            );

            if (!dentro) {
              markerD.setLatLng(markerD.posizionePrecedente);
               return;
                
            } else {
                markerD.posizionePrecedente = nuovaPosizione;
                posizioneModificata = true;
                $('#btnSalvaPosPiazzola').removeClass('disabled');
            }

          });

          return markerD;
        }
      }).addTo(mappiazzola);

      layerPiazzola.addData(geojsonPiazzola);

      /*layerPiazzola.eachLayer(function(marker) {
        marker.bindPopup('Premi il pulsante <i class="fa-solid fa-pencil"></i> per spostare la piazzola');
        marker.openPopup();
      });*/

      // layer asta
      layerAstaPiazzola = L.geoJSON(null, {
          style: function(feature) {
            if (feature.properties.tipo === 'asta') {

                return {
                    color: "black",
                    weight: 5
                };

            }
          }
      }).addTo(mappiazzola);

      //layer buffer intorno ad asta
      layerBufferAsta = L.geoJSON(null, {
          style: function(feature) {
            if (feature.properties.tipo === 'buffer') {
              return {
                  color: "blue",
                  weight: 5,
                  fillColor: "blue",
                  fillOpacity: 0.15
              };
            }
          }
      });

      // carico le geometrie restituite dalla query in asta_piazzola.php nei layer definiti sopra
      fetch(
          './layers/asta_piazzola.php?id_asta=' + id_asta
      )
        .then(response => {
          console.log("Response:", response);
          console.log("Status:", response.status);

          if (!response.ok) {
              throw new Error(
                  'HTTP ' + response.status + ' - ' + response.statusText
              );
          }

          return response.json();
      })
      .then(data => {
          console.log("GeoJSON asta:", data);
          // in data ho due geometrie restituite dalla query, asta e buffer, 
          // le estraggo e le carico nei rispettivi layer
          featureAsta = data.features.filter(
            f => f.properties.tipo === "asta"
          );

          data.features.forEach(f => {

              if (f.properties.tipo === 'buffer') {
                  bufferFeature = f;
              }

          });

          featureBuffer = data.features.filter(
              f => f.properties.tipo === "buffer"
          );

        layerAstaPiazzola.addData(featureAsta);
        layerBufferAsta.addData(featureBuffer);

        bounds = layerBufferAsta.getBounds();

        setTimeout(() => {

            // devo fare il setTimeout per fare in modo che il DOM venga caricato completamente 
            // e quindi la mappa venga disegnata con le dimensioni corretta
            mappiazzola.invalidateSize();

            bounds = layerBufferAsta.getBounds();

            if (bounds.isValid()) {
              // zoom e center al buffer dell'asta
              mappiazzola.fitBounds(bounds, {
                  padding: [15, 15],
                  maxZoom: 19
              });

            } else {
                console.error("Il buffer non contiene una geometria valida");
            }

        }, 200);

      })
      .catch(error => {
          console.error("Errore caricamento asta:", error);
      });


        markerD.bindPopup('Premi il pulsante <i class="fa-solid fa-pencil"></i> per spostare la piazzola');


      mappiazzola.once('moveend', function() {
            markerPiazzola.openPopup();
        });
      
      // essendo la mappa dentro a un tab non attivo leaflet fa un po' di casino con le dimensioni della mappa, 
      // quando il tab viene mostrato bisogna ridisegnare la mappa e impostare la vista sulla piazzola 
      // intercettando l'evento del cambio di tab ovvero "shown.bs.tab" del tab "posizione-tab"
      /*posizioneTab = document.getElementById('posizione-tab');
      posizioneTab.addEventListener('shown.bs.tab', function () {
          setTimeout(() => {
              mappiazzola.invalidateSize();
              mappiazzola.setView(
                  [<?php echo $lat; ?>, <?php echo $lon; ?>],
                  18
              );
          }, 100);
      });*/

      bottoneEdit = L.control({
          position: 'topleft'
      });

      bottoneEdit.onAdd = function() {

          div = L.DomUtil.create(
              'div',
              'leaflet-bar leaflet-control'
          );

          div.innerHTML = `
              <a href="#" id="btnSpostaPiazzola" style="background-color:#0d6efd;color: white;font-size: medium;"
                title="Sposta piazzola"><i class="fa-solid fa-pencil"></i>
              </a>
          `;

          return div;
      };

      bottoneEdit.addTo(mappiazzola);

      bottoneSalvaPos = L.control({
          position: 'topleft'
      });

      bottoneSalvaPos.onAdd = function() {

        div = L.DomUtil.create(
              'div',
              'leaflet-bar leaflet-control'
          );
          

        div.innerHTML = `
            <a href="#" id="btnSalvaPosPiazzola" style="background-color:#0d6efd;color: white;font-size: medium;"
              title="Salva posizione"><i class="fa-solid fa-floppy-disk"></i>
            </a>
        `;

        div.style.display = 'none';

        return div;
      };

      bottoneSalvaPos.addTo(mappiazzola);

      $(document).off('click', '#btnSpostaPiazzola')
      .on('click', '#btnSpostaPiazzola', function(e) {

        e.preventDefault();

        modalitaModifica = !modalitaModifica;

        if (modalitaModifica) {

            posizioneOriginale = markerPiazzola.getLatLng();

            posizioneModificata = false;

            markerPiazzola.dragging.enable();

            layerBufferAsta.addTo(mappiazzola);

            $('#btnSpostaPiazzola').html('<i class="fa-solid fa-arrow-rotate-left"></i>');

            markerPiazzola.closePopup();

            $('#btnSalvaPosPiazzola').parent().show();

            $('#btnSalvaPosPiazzola').addClass('disabled');

        } else {

          // Se ci sono modifiche non salvate
          if (posizioneModificata) {
              markerPiazzola.setLatLng(posizioneOriginale);
              markerPiazzola.posizionePrecedente = posizioneOriginale;
          }

          markerPiazzola.dragging.disable();

          mappiazzola.removeLayer(layerBufferAsta);

          $('#btnSpostaPiazzola').html('<i class="fa-solid fa-pencil"></i>');

          markerPiazzola.openPopup();

          $('#btnSalvaPosPiazzola').parent().hide();

          posizioneModificata = false;

        }

          }
      );



      $(document).on(
          'click',
          '#btnSalvaPosPiazzola',
          function(e) {

              e.preventDefault();

              pos = markerPiazzola.getLatLng();

              console.log('salvo la nuova posizione della piazzola')

              $.ajax({

                  url:
                      './backoffice/salva_posizione_piazzola.php',
                  type: 'POST',
                  data: {
                      id_piazzola: markerPiazzola.feature.properties.id_piazzola,
                      lat: pos.lat,
                      lon: pos.lng
                  },

                  success: function(response) {

                      console.log(response);

                      markerPiazzola.dragging.disable();

                      mappiazzola.removeLayer(
                          layerBufferAsta
                      );

                      $('#btnSalvaPosPiazzola').parent().hide();
                      $('#btnSpostaPiazzola').html('<i class="fa-solid fa-pencil"></i>');

                      modalitaSpostamento = false;

                      showToast(response, 'success', 'map');

                  },

                  error: function(err) {

                      console.error(err);

                      showToast('Errore nel salvataggio', 'error', 'map');

                  }

              });

          }
      );