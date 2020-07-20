<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'enrol_redsys', language 'es'.
 *
 * @package    enrol_redsys
 * @copyright  2020 Hernan Arregoces
 * @author     Hernan Arregoces harregoces@gmail.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['assignrole'] = 'Asignar rol';
$string['businessemail'] = 'Correo corporativo redsys';
$string['businessemail_desc'] = 'La dirección de correo electrónico de su cuenta redsys de negocios';
$string['cost'] = 'Costo de inscripción';
$string['costerror'] = 'El costo de inscripción no es numérico.';
$string['costorkey'] = 'Por favor, elija uno de los siguientes métodos de inscripción.';
$string['currency'] = 'Moneda';
$string['defaultrole'] = 'Asignación de roles por defecto';
$string['defaultrole_desc'] = 'Seleccione el rol que se debe asignar a los usuarios durante las inscripciones en redsys';
$string['enrolenddate'] = 'Fecha final';
$string['enrolenddate_help'] = 'Si está habilitado, los usuarios pueden inscribirse hasta esta fecha solamente.';
$string['enrolenddaterror'] = 'La fecha de finalización de la inscripción no puede ser anterior a la fecha de inicio';
$string['enrolperiod'] = 'Duración de la inscripción';
$string['enrolperiod_desc'] = 'Período predeterminado de tiempo donde la inscripción es válida. Si se establece en cero, la duración de la inscripción será ilimitada de forma predeterminada.';
$string['enrolperiod_help'] = 'La cantidad de tiempo donde la inscripción es válida, comenzando con el momento en que el usuario está inscrito. Si está deshabilitado, la duración de la inscripción será ilimitada.';
$string['enrolstartdate'] = 'Fecha de inicio';
$string['enrolstartdate_help'] = 'Si está habilitado, los usuarios pueden inscribirse a partir de esta fecha solamente.';
$string['errdisabled'] = 'El complemento de inscripción de redsys está deshabilitado y no maneja las notificaciones de pago.';
$string['erripninvalid'] = 'La notificación de pago instantánea no ha sido verificada por Redsys.';
$string['errredsysconnect'] = 'No se pudo conectar a {$ a-> url} para verificar la notificación de pago instantáneo: {$ a-> resultado}';
$string['expiredaction'] = 'Acción de expiración de inscripción';
$string['expiredaction_help'] = 'Seleccione la acción para llevar a cabo cuando caduque la inscripción del usuario. Tenga en cuenta que algunos datos y configuraciones del usuario se eliminan del curso durante la inscripción al curso.';
$string['mailadmins'] = 'Notificar al administrador';
$string['mailstudents'] = 'Notificar a los estudiantes';
$string['mailteachers'] = 'Notificar a los maestros';
$string['messageprovider:redsys_enrolment'] = 'mensajes de inscripción de redsys';
$string['nocost'] = '¡No hay ningún costo asociado con la inscripción en este curso!';
$string['redsys:config'] = 'Configurar las instancias de inscripción de redsys.';
$string['redsys:manage'] = 'Administrar usuarios inscritos';
$string['redsys:unenrol'] = 'Cancelar la inscripción de los usuarios del curso';
$string['redsys:unenrolself'] = 'Anular mi inscripción al curso.';
$string['redsysaccepted'] = 'Pago aceptado por redsys';
$string['pluginname'] = 'Redsys';
$string['pluginname_desc'] = 'El módulo redsys te permite configurar cursos pagados. Si el costo de cualquier curso es cero, no se les pide a los estudiantes que paguen la entrada. Hay un costo en todo el sitio que se establece aquí como predeterminado para todo el sitio y luego una configuración de curso que puede establecer para cada curso individualmente. El costo del curso anula el costo del sitio.';
$string['privacy:metadata:enrol_redsys:enrol_redsys'] = 'Información sobre las transacciones de redsys para inscripciones en redsys.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:business'] = 'Dirección de correo electrónico o ID de cuenta de redsys del destinatario del pago (es decir, el vendedor).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:courseid'] = 'La identificación del curso que se vende.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:instanceid'] = 'El ID de la instancia de inscripción en el curso.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:item_name'] = 'El nombre completo del curso donde se ha vendido su inscripción.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:memo'] = 'Una nota que fue ingresada por el comprador en el campo de nota de pagos del sitio web de redsys.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:option_selection1_x'] = 'Nombre completo del comprador.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:parent_txn_id'] = 'En el caso de un reembolso, revocación o cancelación, este sería el ID de la transacción original.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:payment_status'] = 'El estado del pago.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:payment_type'] = 'Se mantiene si el pago se financió con un eCheck (cheque electrónico) o si se financió con saldo de redsys, tarjeta de crédito o transferencia electrónica (instantánea).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:pending_reason'] = 'La razón por la cual el estado de pago está pendiente (si es así).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:reason_code'] = 'La razón por la cual el estado del pago es Invertido, Reembolsado, Pago cancelado o Negado (si el estado es uno de ellos).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:receiver_email'] = 'Dirección de correo electrónico principal del destinatario del pago (es decir, el vendedor).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:receiver_id'] = 'ID único de cuenta redsys del destinatario del pago (es decir, el vendedor).';
$string['privacy:metadata:enrol_redsys:enrol_redsys:tax'] = 'Cantidad de impuestos cobrados en el pago.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:timeupdated'] = 'La hora en que Redsys notificó a Moodle sobre el pago.';
$string['privacy:metadata:enrol_redsys:enrol_redsys:txn_id'] = 'El número de identificación de transacción original del vendedor para el pago del comprador, contra el cual se registró el caso ';
$string['privacy:metadata:enrol_redsys:enrol_redsys:userid'] = 'La identificación del usuario que compró la inscripción al curso.';
$string['privacy:metadata:enrol_redsys:redsys_com'] = 'El complemento de inscripción de redsys transmite datos del usuario de Moodle al sitio web de redsys.';
$string['privacy:metadata:enrol_redsys:redsys_com:address'] = 'Dirección del usuario que está comprando el curso.';
$string['privacy:metadata:enrol_redsys:redsys_com:city'] = 'Ciudad del usuario que está comprando el curso.';
$string['privacy:metadata:enrol_redsys:redsys_com:country'] = 'País del usuario que está comprando el curso.';
$string['privacy:metadata:enrol_redsys:redsys_com:custom'] = 'Una cadena separada por guiones que contiene el ID del usuario (el comprador), el ID del curso, el ID de la instancia de inscripción.';
$string['privacy:metadata:enrol_redsys:redsys_com:email'] = 'Dirección de correo electrónico del usuario que está comprando el curso.';
$string['privacy:metadata:enrol_redsys:redsys_com:first_name'] = 'Nombre del usuario que está comprando el curso.';
$string['privacy:metadata:enrol_redsys:redsys_com:last_name'] = 'Apellido del usuario que está comprando el curso.';
$string['privacy:metadata:enrol_redsys:redsys_com:os0'] = 'Nombre completo del comprador.';
$string['processexpirationstask'] = 'La tarea de notificación de redsys ha expirado';
$string['sendpaymentbutton'] = 'Enviar pago a través de redsys';
$string['status'] = 'Permitir inscripciones en redsys';
$string['status_desc'] = 'Permitir a los usuarios utilizar redsys para inscribirse en un curso de forma predeterminada.';
$string['transactions'] = 'transacciones de redsys';
$string['unenrolselfconfirm'] = '¿Realmente quieres darte de baja del curso "{$ a}"?';


$string['url'] = 'url';
$string['Ds_Merchant_MerchantCode'] = 'Código del vendedor';
$string['Ds_Merchant_MerchantCode_desc'] = 'Código del vendedor';
$string['Ds_Merchant_Terminal'] = 'Terminal';
$string['Ds_Merchant_Terminal_desc'] = '';
$string['Ds_Merchant_Enc'] = 'Ds_Merchant_Enc';
$string['Ds_Merchant_Enc_desc'] = 'Ds_Merchant_Enc_desc';
$string['payment_type'] = 'Tipo de pago';
$string['payment_unique_type'] = 'Pago único';
$string['payment_recurrent_type'] = 'Pago Recurrente';
$string['payment_type_desc'] = 'Elija el tipo de pago único (pago único) o recurrente (pago múltiple)';
