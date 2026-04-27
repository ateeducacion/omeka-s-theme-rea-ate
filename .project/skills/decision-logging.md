# Skill: Decision Logging (transversal)

## Cuándo usar este skill
Cada vez que un rol vaya a registrar una decisión en alguno de los 
archivos de `.project/decisions/`.

## Reglas generales

- Los archivos de decisiones son **append-only**: nunca se borran 
  entradas, solo se añaden.
- Si una decisión cambia, se añade una nueva entrada que la supera, 
  citando la entrada anterior.
- Cada rol solo escribe en su propio archivo de decisiones.
- El Orquestador puede cambiar el `ESTADO` de decisiones de otros roles 
  (siempre añadiendo una nueva entrada de seguimiento, no modificando 
  la entrada original).

## Formato de entrada

```markdown
## [YYYY-MM-DD] [ESTADO]

**ESTADO** puede ser: `PROPUESTA` | `ACEPTADA` | `RECHAZADA` | `EN REVISIÓN` | `SUPERSEDED`

### Decisión
Descripción concisa de la decisión tomada.

### Contexto
Por qué surgió esta decisión. Qué problema resuelve o qué pregunta responde.

### Alternativas consideradas
- **Opción A**: descripción y motivo de descarte
- **Opción B**: descripción y motivo de descarte

### Consecuencias
Impacto en otros roles, deuda técnica generada, próximos pasos que activa.

### Dependencias
Decisiones de otros roles que bloquean esta, o que esta decisión desbloquea.
```

## Estados

| Estado | Significado |
|--------|-------------|
| `PROPUESTA` | El rol ha registrado la decisión, pendiente de validación |
| `ACEPTADA` | El Orquestador la ha validado; activa para los demás roles |
| `EN REVISIÓN` | Necesita información adicional o aclaración |
| `RECHAZADA` | Descartada; se documenta el motivo |
| `SUPERSEDED` | Sustituida por una decisión posterior (citar cuál) |

## Tabla de estado del Orquestador

El archivo `.project/decisions/orchestrator.md` mantiene al final una 
tabla con el estado actual del proyecto. Esta tabla **se reescribe** 
cada vez que cambia el estado, y es la única excepción a la regla 
append-only.
