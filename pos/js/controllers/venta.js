angular.module('cpm')
.controller('ventaController', ['$scope', '$http', 'ventaServicios', 
	function($scope, $http, ventaServicios){
        $scope.base = "/pavalco/pos/pages/trans";
        $scope.formulario = false;
        $scope.buscadorProducto = false;
        $scope.listaProductos = false;
        $scope.btnItem = false;
        $scope.divPago = false;
        $scope.productos = [];
        $scope.agregados = [];
        $scope.totalVenta = 0;
        $scope.mdlurl = "";
        $scope.verventa = $scope.base + "/ver_venta.html";
        $scope.bodegas = [];
        $scope.ven = [];
        $scope.datosbuscar = {};
        $scope.ventas = [];
        $scope.buscarmas = false;
        $scope.confirmada = false;
        $scope.url_factura = "";
        $scope.metodoPago = [];
		$scope.vista = false;

        $scope.cargarCombos = function() {
            ventaServicios.getMetodoPago().then(function(res) {
                $scope.metodoPago = res;
            });

            ventaServicios.getBodegas().then(function(res) {
                $scope.bodegas = res;
            });
        }

		$scope.mostrarForm = function() {
            $scope.cargarCombos();

            $scope.agregados = [];
            $scope.productos = [];
            $scope.divPago = false;
            
            $scope.mdlurl = $scope.base + "/form_bodega.html";
            $("#mdlPos").modal();
        };

        $scope.iniciarVenta = function(bod) {
            ventaServicios.iniciarVenta(bod).then(function(res){
                if (res.exito == 1) {
                    $("#mdlPos").modal('hide');

                    $scope.setVenta(res.venta);
                } else {
                    alert(res.mensaje);
                }
                
            });
        }
        
        $scope.buscar = function(datos) {
            $scope.formulario = false;

            if (datos) {
                $scope.datosbuscar = {'inicio':0, 'termino': datos.termino};
            } else {
                $scope.datosbuscar = {'inicio':0};
            }
            
            ventaServicios.buscar($scope.datosbuscar).then(function(data){
                $scope.datosbuscar.inicio = data.cantidad;
                $scope.ventas  = data.resultados;
                $scope.resultados = true;

                $scope.ocultarbtn(data.cantidad, data.maximo);
            });
        }

        $scope.mas = function() {
            ventaServicios.buscar($scope.datosbuscar).then(function(data){
                $scope.datosbuscar.inicio += parseInt(data.cantidad);

                $scope.ventas = $scope.ventas.concat(data.resultados);

                $scope.ocultarbtn(data.cantidad, data.maximo);
            });
        }

        $scope.ocultarbtn = function(cantidad, maximo) {
            if ( parseInt(cantidad) < parseInt(maximo) ) {
                $scope.buscarmas = false;
            } else {
                $scope.buscarmas = true;
            }
        }

        $scope.buscarProducto = function(pro) {
			
            pro.idbodega = $scope.ven.idbodega;
			console.log(pro);

            ventaServicios.buscarProducto(pro).then(function(res){
                $scope.productos = res.productos;
                $scope.listaProductos = true;
            });
        }

        $scope.agregarItem = function(item) {
            ventaServicios.agregarDetalle($scope.ven.id, $scope.productos[item]).then(function(res){
                if (res.exito == 0) {
                    alert(res.mensaje);
                } else {
                    $scope.getItems($scope.ven.id);
                }
            });
        }

        $scope.calcularTotal = function() {
            $scope.totalVenta = 0;

            $scope.agregados.forEach(function(item){
                $scope.totalVenta += (parseInt(item.cantidad) * parseFloat(item.precio));
            });
        }

        $scope.quitarItem = function(item) {
            if (confirm("¿Desea continuar?")) {
                ventaServicios.eliminarDetalle(item[1], item).then(function(res) {
                    if (res.exito == 1) {
                        $scope.getItems(item[1]);
                    } else {
                        alert(res.mensaje);
                    } 
                });
            }
        }

        $scope.generarPago = function() {
            if ($scope.agregados.length > 0) {
                if ($scope.divPago) {
                    $scope.divPago = false;
                    $scope.buscadorProducto = true;
                    $scope.btnItem = true;
                } else {
                    $scope.buscadorProducto = false;
                    $scope.btnItem = false;
                    $scope.divPago = true;
                }
            } else {
                alert("Por favor agregue un producto a la venta");
            }
        }

        $scope.buscarNit = function(nit) {
            ventaServicios.buscarNit(nit).then(function(clt) {
                if (clt) {
                    $scope.ven.cliente = clt.id;
                    $scope.ven.nombre = clt.nombre;
                    $scope.ven.direccion = clt.direccion;
                }
            });
        }

        $scope.generarVenta = function(ven) {
            if (confirm("¿Desea continua?")) {
                ven.confirmada = 1;
                ven.total = $scope.totalVenta;
                
                ventaServicios.iniciarVenta(ven).then(function(res) {
                    console.log(res);
                });
            }
        }

        $scope.setVenta = function(venta) {
            $scope.ven = venta;

            $scope.getItems($scope.ven.id);

            if ($scope.ven.confirmada == "1") {
                $scope.formulario = false;
                $scope.buscadorProducto = false;
                $scope.btnItem = false;
                $scope.confirmada = true;
				$scope.vista = true;
                $scope.url_factura = "/pavalco/pos/php/controllers/egreso.php/factura/" + $scope.ven.id;
            } else {
                $scope.confirmada = false;
                $scope.formulario = true;
                $scope.buscadorProducto = true;
                $scope.btnItem = true;
				$scope.vista = false;
            }        
        }

        $scope.getVenta = function(index) {
            $scope.cargarCombos();

            $scope.setVenta($scope.ventas[index]);

            goTop();
        }

        $scope.getItems = function(venta) {
            ventaServicios.verDetalle(venta).then(function(res){
                $scope.agregados = res;
                $scope.calcularTotal();
            })
        }
    }
]);