<?php
/*
    includes/yeapf.i18n.php
    YeAPF 0.8.48-103 built on 2016-05-24 18:54 (-3 DST)
    Copyright (C) 2004-2016 Esteban Daniel Dortta - dortta@yahoo.com
    2016-01-23 22:00:40 (-3 DST)
*/
  _recordWastedTime("Gotcha! ".$dbgErrorCount++);

$GLOBALS['prepositions'] = array ( '�',"ao",'a','ante','at�','ap�s',
     'com','contra','de','desde','em','entre','para','per','perante',
     'por','sem','sob','sobre','tr�s');

$GLOBALS['abbreviations'] = array(
    'ACAD.'=> 'acad�mico',
    'ACAD�M.'=> 'acad�mico',
    'ADV.�'=> 'advogado',
    'ADVO.'=> 'advogado',
    'ALM.'=> 'almirante',
    'ARC.�'=> 'arcebispo',
    'ARCO.'=> 'arcebispo',
    'B.EL'=> 'bacharel',
    'BEL.'=> 'bacharela',
    'B.ELA'=> 'bachar�is',
    'BELA.'=> 'bacharelas',
    'B.�IS'=> 'null',
    'B�IS.'=> 'null',
    'B.ELAS'=> 'null',
    'BELAS.'=> 'null',
    'B.PO'=> 'bispo',
    'BPO.'=> 'bispo',
    'CAP.'=> 'capit�o',
    'CARD.'=> 'cardeal',
    'COM.'=> 'comandante',
    'COM.TE'=> 'comandante',
    'COMTE.'=> 'comandante',
    'COM.'=> 'comendador',
    'COMEND.'=> 'comendador',
    'COM.OR'=> 'comendador',
    'COMOR.'=> 'comendador',
    'C�N.�'=> 'c�nego',
    'C�NO.'=> 'c�nego',
    'CONS.'=> 'conselheiro',
    'CONSEL.'=> 'conselheiro',
    'CONSELH.'=> 'conselheiro',
    'CONS.�'=> 'conselheiro',
    'CONSO.'=> 'conselheiro',
    'CONT.DOR'=> 'contador',
    'CONTDOR.'=> 'contador',
    'CONT.OR'=> 'contador',
    'CONTOR.'=> 'contador',
    'C.-ALM.'=> 'contra-almirante',
    'C.EL'=> 'coronel',
    'CEL.'=> 'coronel',
    'DEP.'=> 'deputado',
    'DES.'=> 'desembargador',
    'DES.�'=> 'desembargadora',
    'DESA.'=> 'null',
    'DI�C.'=> 'di�cono',
    'DD.'=> 'Dign�ssimo',
    'D.'=> 'Digno',
    'D.�'=> 'Dona',
    'DA.'=> 'Dona',
    'D.R'=> 'doutor',
    'DR.'=> 'doutores',
    'D.RS'=> 'null',
    'DRS.'=> 'null',
    'D.RA'=> 'doutora',
    'DRA. D.RAS'=> 'doutoras',
    'DRAS.'=> 'null',
    'E.'=> 'editor',
    'EE.'=> 'editores',
    'E.E.P.'=> 'embaixador extraordin�rio e plenipotenci�rio',
    'EM.�'=> 'Emin�ncia',
    'EMA.'=> 'Emin�ncia',
    'EM.MO'=> 'Eminent�ssimo',
    'EMMO.'=> 'Eminent�ssimo',
    'ENF.'=> 'enfermeiro',
    'ENF.�'=> 'enfermeira',
    'ENFA.'=> 'null',
    'ENG.'=> 'engenheiro',
    'ENG.�'=> 'engenheira',
    'ENGO.'=> 'null',
    'E.E.M.P.'=> 'enviado extraordin�rio e ministro plenipotenci�rio',
    'E.M.'=> 'Estado-Maior',
    'E.-M.'=> 'Estado-Maior',
    'EX.�'=> 'Excel�ncia',
    'EXA.'=> 'Excel�ncia',
    'EX.MO'=> 'Excelent�ssimo',
    'EXMO. EX.MA'=> 'Excelent�ssima',
    'EXMA.'=> 'null',
    'GEN.'=> 'general',
    'G.AL'=> 'general',
    'GAL.'=> 'general',
    'IL.MO'=> 'ilustr�ssimo',
    'ILMO.'=> 'Ilustr�ssima',
    'IL.MA'=> 'null',
    'ILMA.'=> 'null',
    'M.ME'=> 'madame (franc�s = senhora)',
    'MME.'=> 'madame (franc�s = senhora)',
    'M.LLE'=> 'mademoiselle (franc�s = senhorita)',
    'MLLE.'=> 'mademoiselle (franc�s = senhorita)',
    'MAJ.'=> 'major',
    'MAJ.-BRIG.'=> 'major-brigadeiro',
    'MAR.'=> 'marechal',
    'M.AL'=> 'marechal',
    'MAL.'=> 'marechal',
    'M�D.'=> 'm�dico',
    'MM.'=> 'Merit�ssimo',
    'ME'=> 'mestre',
    'ME.'=> 'mestra',
    'M�'=> 'null',
    'MA.'=> 'null',
    'MR.'=> 'mister (ingl�s = senhor)',
    'MONS.'=> 'monsenhor',
    'M.'=> 'monsieur',
    'MM.'=> 'messieurs (franc�s = senhor',
    'M.D.'=> 'Mui(to) Digno',
    'N.S�'=> 'Nossa Senhora',
    'N.SA.'=> 'Nossa Senhora',
    'N.S.'=> 'Nosso Senhor',
    'P.'=> 'padre',
    'P.E'=> 'padre',
    'PE.'=> 'padre',
    'P�R.�'=> 'p�roco',
    'PARO.'=> 'p�roco',
    'PR.'=> 'pastor',
    'PH.D.' => 'PHILOSOPHIAE DOCTOR (LATIM = DOUTOR DE/ EM FILOSOFIA)',
    'PREF.'=> 'prefeito',
    'PRESB.�'=> 'presb�tero',
    'PRESBO.'=> 'presb�tero',
    'PRES.'=> 'presidente',
    'PRESID.'=> 'presidente',
    'PROC.'=> 'procurador',
    'PROF.'=> 'professor',
    'PROFS.'=> 'professores',
    'PROF.�'=> 'professora',
    'PROFA.'=> 'professoras',
    'PROF.AS'=> 'null',
    'PROFAS.'=> 'null',
    'PROM.'=> 'promotor',
    'PROV.'=> 'provedor',
    'R.'=> 'rei',
    'REV.MO'=> 'Reverend�ssimo',
    'REVMO.'=> 'Revend�ssima',
    'REV.MA'=> 'null',
    'REVMA.'=> 'null',
    'REV.'=> 'Reverendo',
    'REV.DO'=> 'Reverendo',
    'REVDO.'=> 'Reverendo',
    'REV.�'=> 'Reverendo',
    'REVO.'=> 'Reverendo',
    'R.P.'=> 'Reverendo Padre',
    'SAC.'=> 'sacerdote',
    'S.'=> 'Santa',
    'S.TA'=> 'Santa',
    'STA.'=> 'Santa',
    'SS.'=> 'Sant�ssimo',
    'S.'=> 'Santo',
    'S.TO'=> 'Santo',
    'STO.'=> 'Santo',
    'S.P.'=> 'Santo Padre',
    'S.'=> 'S�o',
    'SARG.'=> 'sargento',
    'SARG.-AJ.TE'=> 'sargento-ajudante',
    'SARG.-AJTE.'=> 'sargento-ajudante',
    'SEC.'=> 'secret�rio',
    'SECR.'=> 'secret�ria',
    'SEN.'=> 'senador',
    'S.R'=> 'senhor',
    'SR.'=> 'senhores',
    'S.RS'=> 'null',
    'SRS.'=> 'null',
    'S.RA'=> 'senhora',
    'SRA.'=> 'senhoras',
    'S.RAS'=> 'null',
    'SRAS.'=> 'null',
    'SR.TA'=> 'senhorita',
    'SRTA.'=> 'senhoritas',
    'SR.TAS'=> 'null',
    'SRTAS.'=> 'null',
    'S.OR'=> 'S�nior',
    'SOR.'=> 'S�nior',
    'S�R.'=> 's�ror',
    'S.OR'=> 's�ror',
    'SOR.'=> 's�ror',
    'S.A.R.'=> 'Sua Alteza Real',
    'S.A.'=> 'Sua Alteza',
    'S.EM.�'=> 'Sua Emin�ncia',
    'S.EMA.'=> 'Sua Emin�ncia',
    'S..EX.�'=> 'Sua Excel�ncia',
    'S.EXA.'=> 'Sua Excel�ncia',
    'S.EX.� REV.MA'=> 'Sua Excel�ncia Reverend�ssima',
    'S. EXA. REVMA.'=> 'Sua Excel�ncia Reverend�ssima',
    'S.M.'=> 'Sua Majestade',
    'S. REV.�'=> 'Sua Rever�ncia',
    'S.REVA.'=> 'Sua Rever�ncia',
    'S.REV.MA'=> 'Sua Reverend�ssima',
    'S. REVMA.'=> 'Sua Reverend�ssima',
    'S.S.'=> 'Sua Santidade',
    'S.S�'=> 'Sua Senhoria',
    'S.SA.'=> 'Sua Senhoria',
    'TEN.'=> 'tenente',
    'T.TE'=> 'tenente',
    'TTE.'=> 'tenente',
    'TEN. -C.EL'=> 'tenente-coronel',
    'TEN.-CEL.'=> 'tenente-coronel',
    'T.TE - C.EL'=> 'tenente-coronel',
    'TTE. - CEL.'=> 'tenente-coronel',
    'TES.'=> 'tesoureiro',
    'TEST.'=> 'testemunha',
    'VER.'=> 'vereador',
    'VET.'=> 'veterin�rio',
    'V. -ALM.'=> 'vice-almirante',
    'VIG.'=> 'vig�rio',
    'VIG.�'=> 'vig�rio',
    'VIGO.'=> 'vig�rio',
    'V.DE'=> 'visconde',
    'VDE.'=> 'visconde',
    'V.DESSA'=> 'viscondessa',
    'VDESSA.'=> 'viscondessa',
    'V.'=> 'voc�',
    'V.'=> 'voc�',
    'V.A.'=> 'Vossa Alteza',
    'V.EM.�'=> 'Vossa Emin�ncia',
    'V.EMA.'=> 'Vossas Emin�ncias',
    'V.EM.AS'=> 'null',
    'V.EMAS.'=> 'null',
    'V.EX.� REV.MA'=> 'Vossa Excel�ncia Reverend�ssima',
    'V. EXA. REVMA.'=> 'Vossas Excel�ncias Reverend�ssimas',
    'V.EX.AS REV.MAS'=> 'null',
    'V. EXAS. REVMAS.'=> 'null',
    'V.EX.�'=> 'Vossa Excel�ncia',
    'V.EXA.'=> 'Vossas Excel�ncias',
    'V.EX.AS'=> 'null',
    'V.EXAS.'=> 'null',
    'V. MAG.�'=> 'Vossa Magnific�ncia',
    'V.MAGA.'=> 'Vossas Magnific�ncias',
    'V.MAG.AS'=> 'null',
    'V.MAGAS.'=> 'null',
    'V.M.'=> 'Vossa Majestade',
    'V. VER.MA'=> 'Vossa Revend�ssima',
    'V. REVMA.'=> 'Vossas Reverend�ssimas',
    'V.REV.MAS'=> 'null',
    'V. REVMAS.'=> 'null',
    'V.REV.�'=> 'Vossa Rever�ncia',
    'V.REVA.'=> 'Vossas Rever�ncias',
    'V. REV.AS'=> 'null',
    'V.REVAS.'=> 'null',
    'V.S.�'=> 'Vossa Senhoria',
    'V.SA.'=> 'Vossas Senhorias',
    'V.S.AS'=> 'null',
    'V.SAS.'=> 'null');
?>
