<div class="barraNavegacio"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat i clients</h2>
    <div id="titolForm"></div>

    <div class="alert alert-success" id="okMessage" style="display:none" role="alert">
        <div id="okText"></div>
    </div>

    <div class="alert alert-danger" id="errMessage" style="display:none" role="alert">
        <div id="errText"></div>
    </div>

    <form method="POST" action="" class="row g-3" id="formFacturaClient">

        <div class="row g-3">
            <input type="hidden" id="id" name="id" value="" />
            <input type="hidden" id="numero_factura" name="numero_factura" value="" />

            <!-- Selección de cliente -->
            <div class="col-md-4">
                <label for="client_id">Client / Empresa:</label>
                <select class="form-select" name="client_id" id="client_id">
                </select>
            </div>

            <!-- Concepto de la factura -->
            <div class="col-md-4">
                <label for="concepte">Concepte factura:</label>
                <input class="form-control" type="text" name="concepte" id="concepte" />
                <label style="color:#dc3545;display:none" id="concepteCheck">* Invalid data</label>
            </div>

            <!-- Fecha factura -->
            <div class="col-md-4">
                <label for="data_factura">Data de la factura:</label>
                <input class="form-control" type="date" name="data_factura" id="data_factura" />
                <label style="color:#dc3545;display:none" id="dataFacturaCheck">* Missing data</label>
            </div>

            <!-- Fecha vencimiento -->
            <div class="col-md-4">
                <label for="data_venciment">Data de venciment:</label>
                <input class="form-control" type="date" name="data_venciment" id="data_venciment" />
                <label style="color:#dc3545;display:none" id="dataVencimentCheck">* Missing data</label>
            </div>

            <!-- Subtotal -->
            <div class="col-md-4">
                <label for="base_imposable">Import subtotal de la factura (sense IVA):</label>
                <input class="form-control" type="text" name="base_imposable" id="base_imposable" />
                <label style="color:#dc3545;display:none" id="baseImposableCheck">* Missing data</label>
            </div>

            <!-- Despesas extra / fees -->
            <div class="col-md-4">
                <label for="despeses_extra">Càrrecs extres:</label>
                <input class="form-control" type="text" name="despeses_extra" id="despeses_extra" />
                <label style="color:#dc3545;display:none" id="despesesExtraCheck">* Missing data</label>
            </div>

            <!-- Total factura -->
            <div class="col-md-4">
                <label for="total_factura">Import total:</label>
                <input class="form-control" type="text" name="total_factura" id="total_factura" />
                <label style="color:#dc3545;display:none" id="totalFacturaCheck">* Missing data</label>
            </div>

            <!-- IVA -->
            <div class="col-md-4">
                <label for="import_iva">Import IVA:</label>
                <input class="form-control" type="text" name="import_iva" id="import_iva" />
                <label style="color:#dc3545;display:none" id="importIvaCheck">* Missing data</label>
            </div>

            <div class="col-md-4">
                <label for="tipus_iva">Tipus IVA:</label>
                <select class="form-select" name="tipus_iva" id="tipus_iva">
                </select>
            </div>

            <div class="col-md-4">
                <label for="metode_pagament">Mètode de pagament:</label>
                <select class="form-select" name="metode_pagament" id="metode_pagament">
                </select>
            </div>

            <div class="col-md-4">
                <label for="estat">Estat de la factura:</label>
                <select class="form-select" name="estat" id="estat">
                </select>
            </div>

            <div class="col-md-4">
                <label for="emissorId">Emissor factura:</label>
                <select class="form-select" name="emissor_id" id="emissor_id">
                </select>
            </div>

            <div class="col-md-12">
                <label for="notes">Notes factura:</label>
                <input class="form-control" type="text" name="notes" id="notes" />
                <label style="color:#dc3545;display:none" id="notesCheck">* Missing data</label>
            </div>

            <div class="col-md-12">
                <label for="arxiu_url">URL Factura:</label>
                <input class="form-control" type="text" name="arxiu_url" id="arxiu_url" />
                <label style="color:#dc3545;display:none" id="notesCheck">* Missing data</label>
            </div>

            <div class="col-md-4">
                <label for="recurrent">Factura recurrent:</label>
                <input type="checkbox" id="recurrent" name="recurrent" value="1" />
            </div>

            <div class="col-md-4">
                <label for="frequencia">Freqüència:</label>
                <select class="form-select" name="frequencia" id="frequencia" disabled>
                    <option value="">Sense freqüència</option>
                    <option value="mensual">Mensual</option>
                    <option value="trimestral">Trimestral</option>
                    <option value="anual">Anual</option>
                </select>
            </div>

            <div class="col-md-4">
                <label for="projecte_id">Projecte associat a la factura:</label>
                <select class="form-select" name="projecte_id" id="projecte_id">
                </select>
            </div>

            <!-- Sección de detalle de productos -->
            <div class="col-12" style="margin-top:25px">
                <h4>Detall de Productes</h4>
                <table class="table table-bordered" id="tableProductesFactura">
                    <thead>
                        <tr>
                            <th>Producte</th>
                            <th>Preu</th>
                            <th>Descricpió</th>
                            <th><button type="button" class="btn btn-sm btn-success" id="addProducte">Afegir</button></th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Las filas se generarán dinámicamente con JS -->
                    </tbody>
                </table>
            </div>

            <!-- Botón de envío -->
            <div class="container" style="margin-top:25px">
                <div class="row">
                    <div class="col-6 text-left">
                    </div>
                    <div class="col-6 text-right derecha">
                        <button type="submit" class="btn btn-primary" id="btnFactura">Introduir dades</button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>