*=============================================================
*  PROBLEME 1 - Optimisation Discrete
*  Selection du diametre commercial optimal (MIP)
*  Devoir Libre 05 - S. Boulajoul & A. Bourait
*=============================================================
*
*  CORRECTIONS APPORTEES :
*  1. Entites HTML corrigees (&amp; -> & , &gt; -> > , &lt; -> <)
*  2. eps renomme en rugos (eps est un mot reserve GAMS = machine epsilon)
*  3. f pre-calcule comme parametre via Swamee-Jain pour chaque
*     diametre (f etait declare variable sans equation le calculant)
*  3. Toutes les grandeurs hydrauliques pre-calculees par diametre :
*     le modele devient un MIP lineaire (plus robuste que MINLP)
*  4. Option solveur corrigee (MIP = CPLEX suffit)
*  5. gain_vs40 calcule en post-traitement
*=============================================================

$title Optimisation Discrete du Diametre d une Conduite

*-------------------------------------------------------------
*  CONSTANTE PI
*-------------------------------------------------------------
Scalar pi "pi" / 3.14159265358979 / ;

*-------------------------------------------------------------
*  ENSEMBLES
*-------------------------------------------------------------
Sets
    i  "diametres commerciaux disponibles"
       / D1, D2, D3, D4 / ;

*-------------------------------------------------------------
*  PARAMETRES PHYSIQUES ET ECONOMIQUES
*-------------------------------------------------------------
Parameters

*  Parametres physiques
    Q        "debit volumique [m3/s]"          / 2.7778e-3 /
    L        "longueur de la conduite [m]"     / 400       /
    rho      "densite du fluide [kg/m3]"       / 1000      /
    mu       "viscosite dynamique [Pa.s]"      / 0.001     /
    rugos    "rugosite absolue [m]"            / 0.00015   /
    g        "gravite [m/s2]"                  / 9.81      /
    eta      "rendement de la pompe [-]"       / 0.75      /
    H        "hauteur statique [m]"            / 30        /

*  Parametres economiques (en dirhams)
    Cu       "cout unitaire conduite [dh/m2]"  / 2000      /
    Ce       "cout unitaire energie [dh/kWh]"  / 1.2       /
    h        "heures de fonctionnement [h/an]" / 2920      / ;

*-------------------------------------------------------------
*  DIAMETRES COMMERCIAUX [m]
*-------------------------------------------------------------
Parameter dM(i) "diametres commerciaux [m]" ;
    dM("D1") = 0.040 ;
    dM("D2") = 0.050 ;
    dM("D3") = 0.063 ;
    dM("D4") = 0.075 ;

*-------------------------------------------------------------
*  PRE-CALCUL DES GRANDEURS HYDRAULIQUES PAR DIAMETRE
*
*  Principe : pour chaque diametre commercial dM(i), on calcule
*  analytiquement S, V, Re, f (Swamee-Jain), hf, HmT, P, Cinst, Cener.
*  Le modele d'optimisation devient alors un MIP lineaire.
*-------------------------------------------------------------
Parameters
    SM(i)     "section transversale [m2]"
    VM(i)     "vitesse d ecoulement [m/s]"
    ReM(i)    "nombre de Reynolds [-]"
    fM(i)     "coefficient de friction Swamee-Jain [-]"
    hfM(i)    "pertes de charge Darcy-Weisbach [m]"
    HmTM(i)   "hauteur manometrique totale [m]"
    PM(i)     "puissance de la pompe [kW]"
    CinstM(i) "cout d installation [dh]"
    CenerM(i) "cout energetique annuel [dh]"
    CtotM(i)  "cout total annuel [dh]" ;

*  C3 - Section transversale
SM(i)     = (pi * dM(i) * dM(i)) / 4 ;

*  C4 - Vitesse d'ecoulement
VM(i)     = Q / SM(i) ;

*  C5 - Nombre de Reynolds
ReM(i)    = (rho * VM(i) * dM(i)) / mu ;

*  C6 - Coefficient de friction : formule de Swamee-Jain
*        f = 0.25 / [log10(rugos/(3.7*D) + 5.74/Re^0.9)]^2
fM(i)     = 0.25 / ( log10( rugos / (3.7 * dM(i))
                           + 5.74 / (ReM(i)**0.9) ) )**2 ;

*  C7 - Pertes de charge (Darcy-Weisbach)
hfM(i)    = fM(i) * (L / dM(i)) * (VM(i)**2) / (2 * g) ;

*  C8 - Hauteur manometrique totale
HmTM(i)   = H + hfM(i) ;

*  C9 - Puissance de la pompe [kW]
PM(i)     = (rho * g * Q * HmTM(i)) / (eta * 1000) ;

*  Cout d'installation
CinstM(i) = Cu * pi * dM(i) * L ;

*  Cout energetique annuel
CenerM(i) = Ce * PM(i) * h ;

*  Cout total annuel
CtotM(i)  = CinstM(i) + CenerM(i) ;

*  Verification des grandeurs pre-calculees
Display dM, SM, VM, ReM, fM, hfM, HmTM, PM, CinstM, CenerM, CtotM ;

*-------------------------------------------------------------
*  VARIABLES
*-------------------------------------------------------------
Binary Variables
    x(i)     "variable de selection binaire [0 ou 1]" ;

Free Variables
    Ctotal   "cout total annuel [dh]" ;

*-------------------------------------------------------------
*  EQUATIONS (modele MIP lineaire)
*-------------------------------------------------------------
Equations
    eq_obj         "fonction objectif"
    eq_selection   "selection unique d un diametre"
    eq_V_min       "contrainte V >= 0.5 m/s"
    eq_V_max       "contrainte V <= 3.0 m/s"
    eq_Re_min      "contrainte Re >= 4000" ;

*  Fonction objectif (lineaire en x)
eq_obj..
    Ctotal =e= sum(i, x(i) * CtotM(i)) ;

*  Contrainte C1 - selection unique
eq_selection..
    sum(i, x(i)) =e= 1 ;

*  Contrainte vitesse minimale (lineaire en x)
eq_V_min..
    sum(i, x(i) * VM(i)) =g= 0.5 ;

*  Contrainte vitesse maximale (lineaire en x)
eq_V_max..
    sum(i, x(i) * VM(i)) =l= 3.0 ;

*  Contrainte Reynolds minimum (lineaire en x)
eq_Re_min..
    sum(i, x(i) * ReM(i)) =g= 4000 ;

*-------------------------------------------------------------
*  MODELE ET RESOLUTION (MIP lineaire)
*-------------------------------------------------------------
Model Pb1_Discret
    / eq_obj, eq_selection, eq_V_min, eq_V_max, eq_Re_min / ;

Option MIP = CPLEX ;

Solve Pb1_Discret using MIP minimizing Ctotal ;

*-------------------------------------------------------------
*  POST-TRAITEMENT DES RESULTATS
*-------------------------------------------------------------
Scalars
    D_opt      "diametre optimal [m]"
    V_opt      "vitesse optimale [m/s]"
    Re_opt     "Reynolds optimal [-]"
    f_opt      "coefficient de friction [-]"
    hf_opt     "pertes de charge [m]"
    HmT_opt    "hauteur manometrique totale [m]"
    P_opt      "puissance de la pompe [kW]"
    Cinst_opt  "cout d installation [dh]"
    Cener_opt  "cout energetique annuel [dh]"
    D_mm       "diametre optimal [mm]"
    gain_vs40  "gain vs D=40mm [%]" ;

D_opt     = sum(i, x.l(i) * dM(i)) ;
V_opt     = sum(i, x.l(i) * VM(i)) ;
Re_opt    = sum(i, x.l(i) * ReM(i)) ;
f_opt     = sum(i, x.l(i) * fM(i)) ;
hf_opt    = sum(i, x.l(i) * hfM(i)) ;
HmT_opt   = sum(i, x.l(i) * HmTM(i)) ;
P_opt     = sum(i, x.l(i) * PM(i)) ;
Cinst_opt = sum(i, x.l(i) * CinstM(i)) ;
Cener_opt = sum(i, x.l(i) * CenerM(i)) ;
D_mm      = D_opt * 1000 ;

*  Gain relatif par rapport au diametre D1 = 40 mm
gain_vs40 = (CtotM("D1") - Ctotal.l) / CtotM("D1") * 100 ;

Display x.l, D_mm, V_opt, Re_opt, f_opt, hf_opt, HmT_opt,
        P_opt, Cinst_opt, Cener_opt, Ctotal.l, gain_vs40 ;
