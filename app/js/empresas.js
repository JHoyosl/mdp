define(['api','jquery','ko','moment','validate','boostrapDatePicker','bootstrap','ui','moment/locale/es',], function(api,$,ko,moment){
    
    return {
		
		empresasModel: function(){
			
			//lista de empresas
			self.empresaList = ko.observableArray([]);
			
			//add form variables
            self.formAddIdEmpresa = ko.observable("").extend({ uppercase: true });
            self.formAddNombreEmpresa = ko.observable("").extend({ uppercase: true });

			//edit form variables
            self.formEditIdEmpresa = ko.observable("").extend({ uppercase: true });
            self.formEditNombreEmpresa = ko.observable("").extend({ uppercase: true });
			
			//search form variables
            self.idSearch = ko.observable("").extend({ uppercase: true });
            self.nombreSearch = ko.observable("").extend({ uppercase: true });
			

			self.guardarEditarBancos = function(){
				
				var info = {};
				
				info.nit = formEditIdEmpresa();
				info.nombre = formEditNombreEmpresa();

				api.ajaxCom("empresas","editarEmpresa",info,function(response){
					
					
					if(response.data.status){
						
						$("#editEmpresaModal").modal( "hide");
						
					}else{
						
						alert(response.data.info);
						
					}
					self.loadEmpresaTable();
				});
				
			}
			
			self.gaurdarEmpresa = function(){
				
				var info = {};
				
				info.nit = formAddIdEmpresa();
				info.nombre = formAddNombreEmpresa();

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
			// self.loadMaestros();
			//$('#fechaNac').datetimepicker({format: 'YYYY-MM-DD',locale: 'ru'});
        },
	}
	
	
	
})