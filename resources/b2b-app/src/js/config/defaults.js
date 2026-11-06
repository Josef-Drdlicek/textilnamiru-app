/** Výchozí ceníkové parametry (šití + nové položky podhled / LED / doprava) */
export const DEFAULT_VARIABLES = [
  { var_id: '1', name: 'DPH', code: 'VAT', value: '21' },
  { var_id: '2', name: 'Příprava', code: 'PRIPRAVA', value: '33' },
  { var_id: '3', name: 'Obnitkování vrchu', code: 'OBNITKOVANI', value: '7' },
  { var_id: '4', name: 'Stříhání šikmina, oblouk', code: 'SIKMINA', value: '50' },
  { var_id: '5', name: 'Stuha', code: 'STUHA', value: '50' },
  { var_id: '6', name: 'Našití řasící stuhy', code: 'RASICI_STUHA', value: '47' },
  { var_id: '7', name: 'Olůvko', code: 'OLUVKO', value: '50' },
  { var_id: '8', name: 'Všití oluvka', code: 'VSITI_OLUVKO', value: '42' },
  { var_id: '9', name: 'Nadšití záclony', code: 'NADSITI_ZACLONA', value: '45' },
  { var_id: '10', name: 'Lemování', code: 'LEMOVANI', value: '47' },
  { var_id: '11', name: 'Sešití dílů', code: 'SESITI_DILU', value: '47' },
  { var_id: '12', name: 'Ušití záložky / tunýlku', code: 'USITI_ZALOZKA', value: '47' },
  { var_id: '13', name: 'Rohové olůvko 13g', code: 'OLUVKO_13G', value: '22' },
  { var_id: '14', name: 'Rohové olůvko 50g', code: 'OLUVKO_50G', value: '67' },
  { var_id: '15', name: 'Kordovací destička', code: 'KORDOVANI', value: '10' },
  { var_id: '16', name: 'Šneček', code: 'SNECEK', value: '3' },
  { var_id: '17', name: 'Řasení', code: 'RASENI', value: '22' },
  { var_id: '18', name: 'Žehlení, srážení', code: 'ZEHLENI', value: '20' },
  { var_id: '19', name: 'Ušití polštářku', code: 'USITI_POLSTAR', value: '93' },
  { var_id: '20', name: 'Ušití polštářku složitý', code: 'USITI_POLSTAR_C', value: '175' },
  { var_id: '21', name: 'Ušití sedáku', code: 'USITI_SEDAK', value: '105' },
  { var_id: '22', name: 'Ušití úvazu', code: 'USITI_UVAZ', value: '120' },
  { var_id: '23', name: 'Molitan výška 2cm', code: 'MOLITAN_2CM', value: '225' },
  { var_id: '24', name: 'Rouno do sedáku 0,25cm2', code: 'ROUNO_025CM2', value: '38' },
  { var_id: '25', name: 'Výplň duté vlákno 40x40cm', code: 'VLAKNO_40X40CM', value: '130' },
  { var_id: '26', name: 'Výplň duté vlákno 50x50cm', code: 'VLAKNO_50X50CM', value: '195' },
  { var_id: '27', name: 'Kompletace římské rolety', code: 'KOMPLET_ROLETA', value: '300' },
  { var_id: '28', name: 'Polštář rozměr po 10cm', code: 'POLSTAR_10CM', value: '10' },
  { var_id: '29', name: 'Polštář základ ceny', code: 'POLSTAR_ZAKLAD', value: '10' },
  { var_id: '30', name: 'Polštář výplň po 10cm', code: 'POLSTAR_VYPLN_10CM', value: '33' },
  { var_id: '31', name: 'Polštář výplň minimum rozměr', code: 'POLSTAR_VYPLN_MIN', value: '80' },
  { var_id: '32', name: 'Polštář výplň minimum cena', code: 'POLSTAR_VYPLN_MIN_CENA', value: '130' },
  { var_id: '33', name: 'Odšité tunýlky římské rolety', code: 'TUNYLKY_ROLETA', value: '200' },
  // Jednotka (unit) je jen u nových řádků; stávající ceník ji nemá.
  { var_id: '34', name: 'Vypínaný podhled', code: 'VYPINANY_PODHLED', unit: 'm2', value: '1500' },
  { var_id: '35', name: 'LED pásek', code: 'LED_PASEK', unit: 'm', value: '400' },
  { var_id: '36', name: 'Doprava', code: 'DOPRAVA', unit: 'km', value: '12' },
];

/** Typy stuh a koeficienty — ceny jdou upravit v admin ceníku (Řasící stuhy). */
export const STUHY = {
  custom: { label: 'Vlastní', price: 0, ratios: [] },
  siroky70: { label: 'Široký sklad 70mm', price: 51, ratios: ['1.5', '1.85', '2', '2.5'] },
  tuzky85: { label: 'Tužky 85mm', price: 63, ratios: ['1.5', '2', '2.5', '3'] },
  tuzky100: { label: 'Tužky 100mm', price: 66, ratios: ['2', '2.5'] },
  vlna8mm: { label: 'Vlna před kolejnic 8mm', price: 140, ratios: ['1.5', '2', '2.2'] },
  natyc: { label: 'Stuha na tyč', price: 58, ratios: ['1'] },
  double: { label: 'Dvojsklad 70mm', price: 52, ratios: ['2'] },
  triple: { label: 'Trojsklad', price: 52, ratios: ['2'] },
};

export const ORDER_STATUS = {
  2: { label: 'Nové', next: 3, action: 'Příjmout' },
  3: { label: 'Přijaté', next: 4, action: 'K fakturaci' },
  4: { label: 'K fakturaci', next: 5, action: 'Uzavřít' },
  5: { label: 'Hotové', next: null, action: null },
};

export const PRODUCT_TYPES = {
  curtain: { label: 'Záclona', path: 'produkt/zaclona' },
  drape: { label: 'Závěs', path: 'produkt/zaves' },
  pillow: { label: 'Polštář', path: 'produkt/polstar' },
  cushion: { label: 'Sedák', path: 'produkt/sedak' },
  blind: { label: 'Římská roleta', path: 'produkt/roleta' },
};

export const DEMO_USERS = [
  {
    user_id: 1,
    login: 'info@textilnamiru.cz',
    password: 'admin',
    name: 'Administrátor',
    admin: true,
    active: true,
  },
  {
    user_id: 2,
    login: 'lucien',
    password: 'lucien',
    name: 'Lucie',
    admin: false,
    active: true,
  },
];
