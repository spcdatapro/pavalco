angular.module('cpm')
.controller('ingresoController', ['$scope', '$http', 'ingresoServicios', 'bodegaSrvc', 'parteSrvc', 
	function($scope, $http, ingresoServicios, bodegaSrvc, parteSrvc){
        $scope.base = "/pavalco/pos/pages/trans";
        $scope.formulario = false;
        
        $scope.veringreso = $scope.base + "/ver_ingreso.html";
        $scope.vista = false;
        
        $scope.bodegas = [];
        $scope.partes = [];
        $scope.ingresos = [];
        $scope.agregados = [];

        
        $scope.datosbuscar = {};
        $scope.buscarmas = false;
        $scope.confirmado = false;
        $scope.url_factura = "/pos/php/controllers/ingreso.php/factura/";

        $scope.cargarCombos = function() {
            bodegaSrvc.lstBodegas().then(function(res) {
                $scope.bodegas = res;
            });

            parteSrvc.lstPartes().then(function(res) {
                $scope.partes = res;
            });
        }

		$scope.mostrarForm = function() {
            $scope.ing = [];
            $scope.cargarCombos();
            $scope.vista = false;
            $scope.formulario = true;
        };

        $scope.generarIngreso = function(ing) {
            if (confirm("¿Desea continuar?")) {
                ingresoServicios.guardar(ing).then(function(res) {
                    if (res.exito == 1) {
                        $scope.setIngreso(res.ingreso);

                        alert(res.mensaje);
                    } else  {
                        alert(res.mensaje);
                    }
                });
            }
        }
        
        $scope.buscar = function(datos) {
            $scope.formulario = false;
            $scope.vista = false;

            if (datos) {
                $scope.datosbuscar = {'inicio':0, 'termino': datos.termino};
            } else {
                $scope.datosbuscar = {'inicio':0};
            }
            
            ingresoServicios.buscar($scope.datosbuscar).then(function(data){
                $scope.datosbuscar.inicio = data.cantidad;
                $scope.ingresos  = data.resultados;
                $scope.resultados = true;

                $scope.ocultarbtn(data.cantidad, data.maximo);
            });
        }

        $scope.mas = function() {
            ventaServicios.buscar($scope.datosbuscar).then(function(data){
                $scope.datosbuscar.inicio += parseInt(data.cantidad);

                $scope.ingtas = $scope.ingtas.concat(data.resultados);

                $scope.ocultarbtn(data.cantidad, data.maximo);
            });
        }

        $scope.agregarDetalle = function(item) {
            ingresoServicios.agregarDetalle($scope.ing.id, item).then(function(res){
                if (res.exito == 0) {
                    alert(res.mensaje);
                } else {
                    $scope.getItems($scope.ing.id);
                }
            });
        }

        $scope.quitarItem = function(item) {
            if (confirm("¿Desea continuar?") && $scope.ing.id) {
                ingresoServicios.eliminarDetalle(item[1], item).then(function(res) {
                    if (res.exito == 1) {
                        $scope.getItems(item[1]);
                    } else {
                        alert(res.mensaje);
                    } 
               });
            } else {
                alert("Hubo un error, por favor recargue la página e intente nuevamente.");
            }
        }

        $scope.setIngreso = function(ingreso) {
            $scope.ing = ingreso;
            $scope.ing.fecha = new Date($scope.ing.fecha);

            $scope.getItems($scope.ing.id);

            if ($scope.ing.confirmado == 1) {
                $scope.formulario = false;
                $scope.confirmado = true;
                $scope.vista = true;
            } else {
                $scope.confirmado = false;
                $scope.formulario = true;
                $scope.vista = false;
            }
        }

        $scope.getIngreso = function(index) {
            $scope.cargarCombos();
            $scope.setIngreso($scope.ingresos[index]);

            goTop();
        }

        $scope.getItems = function(ingreso) {
            ingresoServicios.verDetalle(ingreso).then(function(res){
                $scope.agregados = res;
            });
        }
    }
]);