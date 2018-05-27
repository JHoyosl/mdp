define(['api','jquery','ko','moment','validate','boostrapDatePicker','bootstrap','ui','moment/locale/es',], function(api,$,ko,moment){
    
    return {
		
		bancosModel: function(){
			
			//lista de ususarios
			self.bancosList = ko.observableArray([]);
			
			//addbancos form variables
            self.formAddIdBanco = ko.observable("").extend({ uppercase: true });
            self.formAddNombreBanco = ko.observable("").extend({ uppercase: true });
			self.fromAddHeadBanco = ko.observable("").extend({ uppercase: true });
			self.formAddRutaBanco = ko.observable().extend({ uppercase: true });
			self.fromAddNotasBanco = ko.observable("").extend({ uppercase: true });

			//addbancos form variables
            self.formEditIdBanco = ko.observable("").extend({ uppercase: true });
            self.formEditNombreBanco = ko.observable("").extend({ uppercase: true });
			self.fromEditHeadBanco = ko.observable("").extend({ uppercase: true });
			self.formEditRutaBanco = ko.observable().extend({ uppercase: true });
			self.fromEditNotasBanco = ko.observable("").extend({ uppercase: true });
			
			//addbancos form variables
            self.idSearch = ko.observable("").extend({ uppercase: true });
            self.nombreSearch = ko.observable("").extend({ uppercase: true });
			self.rutaSearch = ko.observable("").extend({ uppercase: true });
			

			self.guardarEditarBancos = function(){
				
				var info = {};
				
				info.bancoId = formEditIdBanco();
				info.nombre = formEditNombreBanco();
				info.head = fromEditHeadBanco();
				info.ruta = formEditRutaBanco();
				info.notas = fromEditNotasBanco();
				

				api.ajaxCom("bancos","editarBanco",info,function(response){
					
					
					if(response.data.status){
						
						$("#editBancoModal").modal( "hide");
						
					}else{
						
						alert(response.data.info);
						
					}
					self.loadBancosTable();
				});
				
			}
			
			self.gaurdarBanco = function(){
				
				var info = {};
				
				info.bancoId = formAddIdBanco();
				info.nombre = formAddNombreBanco();
				info.head = fromAddHeadBanco();
				info.ruta = formAddRutaBanco();
				info.notas = fromAddNotasBanco();
				
				api.ajaxCom("bancos","crearBanco",info,function(response){
					
					
					if(response.data.status){
						
						alert("Registro modificado");
						$("#addBancoModal").modal( "hide");
						
					}else{
						
						alert(response.data.info);
						
					}
					self.loadBancosTable();
				});
			}
			
			self.addBancoShow = function(){
                
				self.formAddIdBanco("");
				self.formAddNombreBanco("");
				self.fromAddHeadBanco("");
				self.formAddRutaBanco("");
				self.fromAddNotasBanco("");

				$( "#addBancoModal" ).modal( "show");
            };
			
			self.editarBanco = function(banco){
				
				self.formEditIdBanco(banco.BANCO_ID);
	            self.formEditNombreBanco(banco.HEAD);
				self.fromEditHeadBanco(banco.NOMBRE);
				self.formEditRutaBanco(banco.RUTA);
				self.fromEditNotasBanco(banco.NOTAS);

				$( "#editBancoModal" ).modal( "show");
			}
			
			
			self.loadBancosTable = function(){
				
				var info = {};

				info.bancoId = self.idSearch();
				info.nombre = self.nombreSearch();
				info.ruta = self.rutaSearch();

				api.ajaxCom("bancos","getBancosList",info,function(response){
					
					if(!response.data.status){
							
						alert("Error cargando Información");
						return;
					}
					
					var table = response.data.message;

					self.bancosList(table);
					

				});
				
			}
			 
		},
		initialize: function(){

        	
        	ko.applyBindings(new this.bancosModel(), document.getElementById("bancosModel"));   
			
			self.loadBancosTable();
			// self.loadMaestros();
			//$('#fechaNac').datetimepicker({format: 'YYYY-MM-DD',locale: 'ru'});
        },
	}
	
	
	
})