define(['api','jquery','ko','moment','validate','boostrapDatePicker','bootstrap','ui','moment/locale/es',], function(api,$,ko,moment){
    
    return {
		
		empresasModel: function(){

			//lista de empresas
			self.empresaList = ko.observableArray([]);
			self.deptoList = ko.observableArray([]);
			self.ciudadList = ko.observableArray([]);
			
			//add form variables
            self.formAddIdEmpresa = ko.observable("").extend({ uppercase: true });
            self.formAddNombreEmpresa = ko.observable("").extend({ uppercase: true });
            self.addSelectedDepto = ko.observable();
            self.addSelectedCiudad = ko.observable();
            self.formAddSector = ko.observable("").extend({ uppercase: true });
			self.formAddDireccion = ko.observable("").extend({ uppercase: true });
			self.formAddTelefono = ko.observable("").extend({ uppercase: true });

			//edit form variables
            self.formEditIdEmpresa = ko.observable("").extend({ uppercase: true });
            self.formEditNombreEmpresa = ko.observable("").extend({ uppercase: true });
            self.editSelectedDepto = ko.observable("");
            self.editSelectedCiudad = ko.observable("");
            self.formEditSector = ko.observable("").extend({ uppercase: true });
			self.formEditDireccion = ko.observable("").extend({ uppercase: true });
			self.formEditTelefono = ko.observable("").extend({ uppercase: true });
			
			//search form variables
            self.idSearch = ko.observable("").extend({ uppercase: true });
            self.nombreSearch = ko.observable("").extend({ uppercase: true });
			
			self.getAddCiudades = function(){
				

				api.getCiudades(self.addSelectedDepto().id, self.ciudadList);

			}

			self.getEditCiudades = function(){
				
				if(self.editSelectedDepto() != undefined){

					api.getCiudades(self.editSelectedDepto(), self.ciudadList);

				}

			}
			
			self.loadMaestros = function(){
				
				api.getDeptos(self.deptoList);
				
			}
			
			
			self.guardarEditarEmpresa = function(){
				
				var info = {};
				
				info.nit = formEditIdEmpresa();
				info.nombre = formEditNombreEmpresa();
				info.sector = formEditSector();
				info.direccion = formEditDireccion();
				info.telefono = formEditDireccion();
				info.depto = editSelectedDepto();
				info.ciudad = editSelectedCiudad();

				api.ajaxCom("empresas","editarEmpresa",info,function(response){
					
					if(response.data.status){
						
						$("#editEmpresaModal").modal( "hide");
						
					}else{
						
						alert(response.data.info);
						
					}
					self.loadEmpresaTable();
				});
				
			}
			
			self.guardarEmpresa = function(){
				
				var info = {};
				
				info.nit = self.formAddIdEmpresa();
				info.nombre = self.formAddNombreEmpresa();
				info.sector = self.formAddSector();
				info.direccion = self.formAddDireccion();
				info.depto = self.addSelectedDepto().id;
				info.ciudad = self.addSelectedCiudad().id;
				info.telefono = self.formAddTelefono();

				api.ajaxCom("empresas","crearEmpresa",info,function(response){
					
					console.log(response);
					if(response.data.status){
						
						alert("Registro guardado");
						$("#addEmpresaModal").modal( "hide");
						
					}else{
						
						alert(response.data.message);
						
					}
					self.loadEmpresaTable();
				});
			}
			
			self.addEmpresaShow = function(){
                
				self.formAddIdEmpresa("");
				self.formAddNombreEmpresa("");

				$( "#addEmpresaModal" ).modal( "show");
            };
			
			self.editarEmpresa = function(empresa){
				
				self.formEditIdEmpresa(empresa.NIT);
				self.formEditNombreEmpresa(empresa.RAZON_SOCIAL);
				self.formEditSector(empresa.SECTOR);
				self.formEditDireccion(empresa.DIRECCION);
				self.formEditTelefono(empresa.TELEFONO);				
				self.editSelectedDepto(empresa.DEPTO_ID);

				api.getCiudades(empresa.DEPTO_ID,ciudadList,self.editSelectedCiudad,empresa.CIUDAD_ID);
				
				$( "#editEmpresaModal" ).modal( "show");

			}
			
			
			self.loadEmpresaTable = function(){
				
				var info = {};

				info.nit = self.idSearch();
				info.nombre = self.nombreSearch();

				api.ajaxCom("empresas","getEmpresasList",info,function(response){
					
					if(!response.data.status){
							
						alert("Error cargando Información");
						return;
					}
					
					var table = response.data.message;

					self.empresaList(table);
					

				});
				
			}
			 
		},
		initialize: function(){

        	
        	ko.applyBindings(new this.empresasModel(), document.getElementById("empresasModel"));   
			
			self.loadEmpresaTable();
			self.loadMaestros();
			//$('#fechaNac').datetimepicker({format: 'YYYY-MM-DD',locale: 'ru'});
        },
	}
	
	
	
})