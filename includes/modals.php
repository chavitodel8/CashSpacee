<!-- Modal de Recarga -->
<div id="recargaModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Recargar Saldo</h3>
            <button class="modal-close" onclick="closeModal('recargaModal')">&times;</button>
        </div>
        <form id="recargaForm" onsubmit="submitRecarga(event)">
            <!-- Recordatorio de Recarga -->
            <div class="recordatorio-box" style="background: #ffffff; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
                <h4 style="color: #6b7280; font-size: 14px; font-weight: 500; margin: 0 0 12px 0;">Recordatorio importante:</h4>
                <p style="color: #dc2626; font-weight: 700; font-size: 14px; margin: 0 0 16px 0;">Horario de recarga: de 10:00 a 22:00.</p>
                <ol style="margin: 0; padding-left: 20px; color: #374151; font-size: 13px; line-height: 1.8;">
                    <li style="margin-bottom: 8px;">El monto mínimo de recarga es de Bs 100.</li>
                    <li style="margin-bottom: 8px;">Si la recarga se realizó correctamente pero el saldo no se acredita dentro de los 5 minutos, comuníquese de inmediato con nuestro servicio de atención al cliente y proporcione el comprobante de pago.</li>
                    <li style="margin-bottom: 8px;">No utilice cuentas de pago antiguas para realizar transferencias.</li>
                    <li style="margin-bottom: 8px;">No transfiera dinero a personas desconocidas. Todas las recargas deben realizarse únicamente a través de la aplicación oficial.</li>
                    <li style="margin-bottom: 0;">Está terminantemente prohibido compartir su comprobante de recarga con terceros, para evitar filtraciones o pérdidas de información.</li>
                </ol>
            </div>
            
            <div class="form-group">
                <label class="form-label">Monto a Recargar (Bs)</label>
                <select name="monto" class="form-control" required id="recargaMonto" onchange="if(typeof generarQR === 'function') { const metodo = document.getElementById('metodoPagoRecarga'); if(metodo && metodo.value === 'yape') generarQR(); }">
                    <option value="">Selecciona un monto</option>
                    <option value="100">100,00 Bs</option>
                    <option value="200">200,00 Bs</option>
                    <option value="500">500,00 Bs</option>
                    <option value="1000">1.000,00 Bs</option>
                    <option value="2000">2.000,00 Bs</option>
                    <option value="5000">5.000,00 Bs</option>
                    <option value="10000">10.000,00 Bs</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Método de Pago</label>
                <select name="metodo_pago" class="form-control" required id="metodoPagoRecarga" onchange="if(window.mostrarMetodoPago) mostrarMetodoPago();">
                    <option value="yape" selected>Yape</option>
                    <option value="transferencia">Transferencia Bancaria</option>
                </select>
            </div>
            
            <!-- Sección para QR de Yape -->
            <div id="seccionQR" class="form-group" style="display: none;">
                <label class="form-label">Código QR de Pago Yape</label>
                <div style="text-align: center; padding: 20px; background: #f9fafb; border-radius: 10px; margin-top: 10px;">
                    <div id="qrCodeContainer">
                        <p style="color: #6b7280; font-size: 14px;">Selecciona un monto para generar el QR</p>
                    </div>
                </div>
                <small style="color: #6b7280; font-size: 12px; display: block; margin-top: 10px; text-align: center;">
                    Escanea el código QR con Yape para realizar el pago
                </small>
            </div>
            
            <!-- Sección para Transferencia Bancaria (solo información) -->
            <div id="seccionTransferencia" class="form-group" style="display: none;">
                <div style="background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 8px; padding: 15px; margin-top: 10px;">
                    <p style="margin: 0; color: #0369a1; font-size: 14px; font-weight: 600;">
                        <i class="fas fa-info-circle"></i> Información de Transferencia Bancaria
                    </p>
                    <p style="margin-top: 10px; color: #0369a1; font-size: 13px;">
                        Realiza la transferencia bancaria y adjunta el comprobante al enviar la solicitud.
                    </p>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="3" placeholder="Detalles adicionales sobre la recarga"></textarea>
            </div>
            <div id="recargaMessage"></div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Enviar Solicitud
            </button>
        </form>
    </div>
</div>

<!-- Modal de Canjear Código -->
<div id="codigoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Canjear Código Promocional</h3>
            <button class="modal-close" onclick="closeModal('codigoModal')">&times;</button>
        </div>
        <form id="codigoForm" onsubmit="submitCodigo(event)">
            <div class="form-group">
                <label class="form-label">Código Promocional</label>
                <input type="text" name="codigo" class="form-control" placeholder="Ingresa el código" required>
            </div>
            <div id="codigoMessage"></div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-ticket-alt"></i> Canjear Código
            </button>
        </form>
    </div>
</div>

<!-- Modal de Retiro -->
<div id="retiroModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Solicitar Retiro</h3>
            <button class="modal-close" onclick="closeModal('retiroModal')">&times;</button>
        </div>
        <form id="retiroForm" onsubmit="submitRetiro(event)">
            <!-- Recordatorio de Retiro -->
            <div class="recordatorio-box" style="background: #ffffff; border-radius: 12px; padding: 20px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
                <h4 style="color: #6b7280; font-size: 14px; font-weight: 500; margin: 0 0 16px 0;">Recordatorio importante:</h4>
                <ol style="margin: 0; padding-left: 20px; color: #374151; font-size: 13px; line-height: 1.8;">
                    <li style="margin-bottom: 8px;">Puede solicitar un retiro entre las 10:00 a.m. y las 10:00 p.m.</li>
                    <li style="margin-bottom: 8px;">El monto mínimo de retiro es de Bs 150.</li>
                    <li style="margin-bottom: 8px;">Se aplicará una comisión del 5% por cada retiro (IVA incluido).</li>
                    <li style="margin-bottom: 8px;">Una vez confirmada la solicitud, el retiro se procesará en un plazo de 1 a 24 horas.</li>
                    <li style="margin-bottom: 8px;">Los retiros solo se habilitarán después de una inversión mínima de Bs 100 en un dispositivo.</li>
                    <li style="margin-bottom: 0;">No hay límite en la cantidad de retiros que puede realizar por día.</li>
                </ol>
            </div>
            
            <div class="form-group">
                <label class="form-label">Monto a Retirar (Bs) - Mínimo: 150 Bs</label>
                <input type="number" name="monto" class="form-control" placeholder="150" min="150" step="0.01" required id="retiroMonto">
                <small style="color: #6b7280; font-size: 12px;">Tu saldo disponible: <span id="saldoDisponible">-</span></small>
            </div>
            <div class="form-group">
                <label class="form-label">Método de Pago</label>
                <select name="metodo_pago" class="form-control" required id="metodoPagoRetiro">
                    <option value="transferencia" selected>Transferencia Bancaria</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Cuenta de Destino</label>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input type="text" name="cuenta_destino" class="form-control" placeholder="Número de cuenta bancaria" required id="cuentaDestinoRetiro" style="flex: 1;">
                    <button type="button" class="btn btn-outline" onclick="cargarDatosBancarios()" style="white-space: nowrap;">
                        <i class="fas fa-sync-alt"></i> Usar cuenta configurada
                    </button>
                </div>
                <div id="infoCuentaBancaria" style="margin-top: 10px; padding: 10px; background: #f0f9ff; border-radius: 8px; display: none;">
                    <small style="color: #0369a1; font-size: 12px;">
                        <strong>Cuenta configurada:</strong> <span id="cuentaConfigurada">-</span><br>
                        <strong>Tipo:</strong> <span id="tipoCuentaConfigurada">-</span>
                    </small>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Observaciones</label>
                <textarea name="observaciones" class="form-control" rows="3" placeholder="Detalles adicionales"></textarea>
            </div>
            <div id="retiroMessage"></div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-money-bill-wave"></i> Solicitar Retiro
            </button>
        </form>
    </div>
</div>

