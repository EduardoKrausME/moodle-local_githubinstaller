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
 * local_githubinstaller.php
 *
 * @package   local_githubinstaller
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['archivesize'] = 'O ZIP excede os limites de segurança: 50 MB compactado ou 200 MB descompactado.';
$string['autodeploydisabled'] = 'A instalação automática de plugins está desativada neste Moodle pela configuração disableupdateautodeploy.';
$string['branch'] = 'Branch';
$string['commit'] = 'Commit';
$string['component'] = 'Componente';
$string['continuevalidation'] = 'Continuar para validação e aprovação do Moodle';
$string['currentversion'] = 'Versão instalada';
$string['downgradeblocked'] = 'A versão do GitHub é inferior à versão instalada. Reversões de versão não são permitidas por este instalador.';
$string['downloadfailed'] = 'O download do arquivo ZIP do GitHub falhou. Verifique a referência e a conectividade do servidor.';
$string['githubmetadataerror'] = 'Não foi possível consultar a branch padrão no GitHub. Verifique se o repositório é público e se a API do GitHub está disponível.';
$string['install'] = 'Nova instalação';
$string['introduction'] = 'Informe o endereço de um repositório público do GitHub para baixar um plugin. A instalação ou atualização só será executada depois das verificações e da confirmação no instalador oficial do Moodle.';
$string['invalidarchive'] = 'O arquivo recebido não possui a estrutura ZIP esperada de um repositório GitHub.';
$string['invalidcomponent'] = 'O componente declarado no version.php não é um plugin válido do Moodle.';
$string['invalidref'] = 'A branch, tag ou SHA informado é inválido.';
$string['invalidrepository'] = 'Informe uma URL HTTPS de repositório válida, no formato https://github.com/usuario/repositorio. Links de arquivos, páginas de branches e domínios alternativos não são aceitos.';
$string['invalidstorage'] = 'O arquivo temporário do instalador não foi encontrado ou é inválido. Baixe o plugin novamente.';
$string['missingversion'] = 'Não foi encontrado um version.php válido na raiz do plugin, com componente e versão declarados diretamente.';
$string['newversion'] = 'Versão do ZIP';
$string['notinstalled'] = 'Não instalado';
$string['operation'] = 'Operação';
$string['pluginname'] = 'Instalador de plugins do GitHub';
$string['prepare'] = 'Baixar e verificar';
$string['privacy:metadata'] = 'O instalador não armazena dados pessoais em tabelas próprias do Moodle.';
$string['ref'] = 'Branch, tag ou SHA do commit';
$string['ref_help'] = 'Deixe em branco para utilizar a branch padrão do repositório. Para instalar uma tag ou commit específico, selecione o tipo e informe a referência. Para commits, utilize o SHA completo de 40 caracteres.';
$string['refplaceholder'] = 'Em branco = branch padrão';
$string['reftype'] = 'Origem do código';
$string['repository'] = 'URL do repositório no GitHub';
$string['reviewheading'] = 'Revisão do pacote GitHub';
$string['reviewintro'] = 'O pacote foi baixado e sua identificação foi verificada. Confira os dados antes de seguir para a validação completa do Moodle.';
$string['reviewwarning'] = 'A próxima tela será a validação oficial do Moodle. Nenhum arquivo de plugin foi instalado até aqui. Instale somente código de repositórios confiáveis e faça backup antes de atualizar plugins existentes.';
$string['sameversionwarning'] = 'O número de versão não mudou. É possível que existam alterações de código, mas o Moodle não executará uma atualização de banco de dados apenas por substituir arquivos de mesma versão.';
$string['selfupdate'] = 'Por segurança, este plugin não pode atualizar a si mesmo durante a execução. Atualize-o pelo instalador ZIP oficial ou pelo servidor.';
$string['standardplugin'] = 'Não é permitido substituir um plugin que faz parte da distribuição padrão do Moodle.';
$string['tag'] = 'Tag';
$string['update'] = 'Atualização';
$string['ziprequired'] = 'A extensão PHP ZipArchive é necessária para verificar o arquivo baixado.';
