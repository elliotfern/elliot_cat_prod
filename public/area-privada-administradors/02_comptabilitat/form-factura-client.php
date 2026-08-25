<div id="barraNavegacioContenidor"></div>

<div class="container-fluid form">

    <h2>Gestió comptabilitat i clients</h2>

    <div id="titolForm"></div>

    <!-- Mensaje OK -->
    <div class="alert alert-success d-none" id="okMessage" role="alert">
        <div id="okText"></div>
    </div>

    <!-- Mensaje ERROR -->
    <div class="alert alert-danger d-none" id="errMessage" role="alert">
        <div id="errText"></div>
    </div>

    <form
        method="POST"
        action=""
        class="row g-3"
        id="formFacturaClient">

        <div class="row g-3">

            <!-- Campos ocultos -->
            <input
                type="hidden"
                id="id"
                name="id"
                value="" />

            <input
                type="hidden"
                id="numero_factura"
                name="numero_factura"
                value="" />


            <!-- =====================================================
                 CLIENTE
            ====================================================== -->

            <div class="col-md-4">
                <label for="client_id" class="form-label">
                    Client / Empresa:
                </label>

                <select
                    class="form-select"
                    name="client_id"
                    id="client_id">
                </select>
            </div>


            <!-- =====================================================
                 CONCEPTO
            ====================================================== -->

            <div class="col-md-4">
                <label for="concepte" class="form-label">
                    Concepte factura:
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="concepte"
                    id="concepte" />

                <div
                    class="text-danger d-none"
                    id="concepteCheck">
                    * Invalid data
                </div>
            </div>


            <!-- =====================================================
                 FECHA FACTURA
            ====================================================== -->

            <div class="col-md-4">
                <label for="data_factura" class="form-label">
                    Data de la factura:
                </label>

                <input
                    class="form-control"
                    type="date"
                    name="data_factura"
                    id="data_factura" />

                <div
                    class="text-danger d-none"
                    id="dataFacturaCheck">
                    * Missing data
                </div>
            </div>


            <!-- =====================================================
                 FECHA VENCIMIENTO
            ====================================================== -->

            <div class="col-md-4">
                <label for="data_venciment" class="form-label">
                    Data de venciment:
                </label>

                <input
                    class="form-control"
                    type="date"
                    name="data_venciment"
                    id="data_venciment" />

                <div
                    class="text-danger d-none"
                    id="dataVencimentCheck">
                    * Missing data
                </div>
            </div>


            <!-- =====================================================
                 SUBTOTAL
            ====================================================== -->

            <div class="col-md-4">
                <label for="base_imposable" class="form-label">
                    Import subtotal de la factura (sense IVA):
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="base_imposable"
                    id="base_imposable" />

                <div
                    class="text-danger d-none"
                    id="baseImposableCheck">
                    * Missing data
                </div>
            </div>


            <!-- =====================================================
                 CARGOS EXTRA
            ====================================================== -->

            <div class="col-md-4">
                <label for="despeses_extra" class="form-label">
                    Càrrecs extres:
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="despeses_extra"
                    id="despeses_extra" />

                <div
                    class="text-danger d-none"
                    id="despesesExtraCheck">
                    * Missing data
                </div>
            </div>


            <!-- =====================================================
                 TOTAL
            ====================================================== -->

            <div class="col-md-4">
                <label for="total_factura" class="form-label">
                    Import total:
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="total_factura"
                    id="total_factura" />

                <div
                    class="text-danger d-none"
                    id="totalFacturaCheck">
                    * Missing data
                </div>
            </div>


            <!-- =====================================================
                 IVA
            ====================================================== -->

            <div class="col-md-4">
                <label for="import_iva" class="form-label">
                    Import IVA:
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="import_iva"
                    id="import_iva" />

                <div
                    class="text-danger d-none"
                    id="importIvaCheck">
                    * Missing data
                </div>
            </div>


            <!-- =====================================================
                 TIPO IVA
            ====================================================== -->

            <div class="col-md-4">
                <label for="tipus_iva" class="form-label">
                    Tipus IVA:
                </label>

                <select
                    class="form-select"
                    name="tipus_iva"
                    id="tipus_iva">
                </select>
            </div>


            <!-- =====================================================
                 METODO PAGO
            ====================================================== -->

            <div class="col-md-4">
                <label for="metode_pagament" class="form-label">
                    Mètode de pagament:
                </label>

                <select
                    class="form-select"
                    name="metode_pagament"
                    id="metode_pagament">
                </select>
            </div>


            <!-- =====================================================
                 ESTADO
            ====================================================== -->

            <div class="col-md-4">
                <label for="estat" class="form-label">
                    Estat de la factura:
                </label>

                <select
                    class="form-select"
                    name="estat"
                    id="estat">
                </select>
            </div>


            <!-- =====================================================
                 EMISOR
            ====================================================== -->

            <div class="col-md-4">
                <label for="emissor_id" class="form-label">
                    Emissor factura:
                </label>

                <select
                    class="form-select"
                    name="emissor_id"
                    id="emissor_id">
                </select>
            </div>


            <!-- =====================================================
                 NOTAS
            ====================================================== -->

            <div class="col-md-12">
                <label for="notes" class="form-label">
                    Notes factura:
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="notes"
                    id="notes" />

                <div
                    class="text-danger d-none"
                    id="notesCheck">
                    * Missing data
                </div>
            </div>


            <!-- =====================================================
                 URL FACTURA
            ====================================================== -->

            <div class="col-md-12">
                <label for="arxiu_url" class="form-label">
                    URL Factura:
                </label>

                <input
                    class="form-control"
                    type="text"
                    name="arxiu_url"
                    id="arxiu_url" />

                <!-- ID CORREGIDO: antes estaba repetido notesCheck -->
                <div
                    class="text-danger d-none"
                    id="arxiuUrlCheck">
                    * Missing data
                </div>
            </div>


            <!-- =====================================================
                 FACTURA RECURRENTE
            ====================================================== -->

            <div class="col-md-4">
                <div class="form-check mt-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="recurrent"
                        name="recurrent"
                        value="1" />

                    <label
                        class="form-check-label"
                        for="recurrent">
                        Factura recurrent
                    </label>

                </div>
            </div>


            <!-- =====================================================
                 FRECUENCIA
            ====================================================== -->

            <div class="col-md-4">
                <label for="frequencia" class="form-label">
                    Freqüència:
                </label>

                <select
                    class="form-select"
                    name="frequencia"
                    id="frequencia"
                    disabled>
                    <option value="">
                        Sense freqüència
                    </option>

                    <option value="mensual">
                        Mensual
                    </option>

                    <option value="trimestral">
                        Trimestral
                    </option>

                    <option value="anual">
                        Anual
                    </option>
                </select>
            </div>


            <!-- =====================================================
                 PROYECTO
            ====================================================== -->

            <div class="col-md-4">
                <label for="projecte_id" class="form-label">
                    Projecte associat a la factura:
                </label>

                <select
                    class="form-select"
                    name="projecte_id"
                    id="projecte_id">
                </select>
            </div>


            <!-- =====================================================
                 PRODUCTOS
            ====================================================== -->

            <div class="col-12 mt-4">

                <h4>
                    Detall de Productes
                </h4>

                <div class="table-responsive">

                    <table
                        class="table table-bordered"
                        id="tableProductesFactura">

                        <thead>
                            <tr>

                                <th>
                                    Producte
                                </th>

                                <th>
                                    Preu
                                </th>

                                <th>
                                    Descripció
                                </th>

                                <th>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-success"
                                        id="addProducte">
                                        Afegir
                                    </button>
                                </th>

                            </tr>
                        </thead>

                        <tbody>
                            <!--
                                Las filas de productos
                                se generan dinámicamente
                                mediante TypeScript.
                            -->
                        </tbody>

                    </table>

                </div>

            </div>


            <!-- =====================================================
                 BOTÓN GUARDAR
            ====================================================== -->

            <div class="container mt-4">

                <div class="row">

                    <div class="col-6 text-start">
                    </div>

                    <div class="col-6 text-end derecha">

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="btnFactura">
                            Introduir dades
                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>