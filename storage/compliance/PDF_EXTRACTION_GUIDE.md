# PDF STRUCTURE EXTRACTION GUIDE

## Purpose
Extract exact structural components from official government reference PDFs to replicate in Blade templates.

## Prerequisites
- Reference PDFs placed in: `/storage/compliance/reference_pdfs/`
- PDF viewer with text selection capability
- Ruler tool for measuring margins and spacing

---

## EXTRACTION PROCESS

### Step 1: Document Identification
1. Open reference PDF
2. Note file name
3. Identify form code (e.g., FORM_B, FORM_XIII)
4. Take screenshot for visual reference

### Step 2: Header Extraction

**Extract:**
- Form title (exact text, capitalization, punctuation)
- Act reference (exact wording in brackets)
- Rule reference (exact wording in brackets)
- Any additional header text

**Measure:**
- Font size (approximate in pt)
- Line spacing
- Alignment (left/center/right)
- Margin from top

**Example:**
```
FORM TITLE: "FORM B - REGISTER OF WAGES"
Font: Bold, 14pt, Uppercase
Alignment: Center
Act Reference: "[Under Section 13 of the Factories Act, 1948]"
Font: Regular, 10pt
Rule Reference: "[See Rule 26 of the Factories Rules]"
```

### Step 3: Establishment Block Extraction

**Extract:**
- Field labels (exact wording)
- Field order (top to bottom)
- Label width
- Layout style (table/list)

**Example:**
```
Name of Factory: _______________
Address: _______________
Registration/License No: _______________
Wage Period: _______________

Layout: Two-column table
Label width: 180px
```

### Step 4: Table Structure Extraction

**Critical - Extract Exactly:**
1. Column count
2. Column order (left to right)
3. Column headings (exact text, no paraphrasing)
4. Column widths (percentage or fixed)
5. Header row style (bold, background color)
6. Cell padding
7. Border style

**Example:**
```
Columns: 13
Order: S.No. | Employee Code | Name | Designation | Basic | DA | HRA | OT | Gross | PF | ESI | Deductions | Net

Column 1: "S.No." - 3% - Center aligned
Column 2: "Employee Code" - 8% - Left aligned
Column 3: "Name of Worker" - 15% - Left aligned
...

Header: Bold, Center aligned, 1px solid border
Cells: 1px solid border, 4px padding
```

### Step 5: Totals Row Extraction

**Extract:**
- Label text (e.g., "TOTAL", "Grand Total")
- Label position (which column)
- Label alignment
- Format (bold, background, underline)
- Which columns have totals

**Example:**
```
Label: "TOTAL"
Position: Spans columns 1-4
Alignment: Right
Format: Bold, no background
Totals in: Columns 5-13
```

### Step 6: NIL Declaration Extraction

**Extract:**
- Exact text when no data
- Border style
- Padding
- Font size and weight
- Alignment

**Example:**
```
Text: "NIL - No wages paid during this period"
Border: 1px solid black
Padding: 40px
Font: Bold, 11pt
Alignment: Center
```

### Step 7: Declaration Section Extraction

**Extract:**
- Declaration text (word-for-word)
- Font size
- Line spacing
- Margin from table

**Example:**
```
Text: "I hereby certify that the above particulars are correct to the best of my knowledge and belief."
Font: Regular, 10pt
Line spacing: 1.6
Margin top: 30px
```

### Step 8: Signature Block Extraction

**Extract:**
- Layout (left/right split or single column)
- Left side content (Date, Place, etc.)
- Right side content (Signature line, Name, Designation)
- Signature line width
- Signature line margin from text
- Seal position and text

**Example:**
```
Layout: Two-column table

Left column (50%):
- Date: _______________
- Place: _______________

Right column (50%):
- Signature line (200px wide, 60px margin top)
- "Signature of Manager/Occupier"
- Name: _______________
- Designation: _______________
- "(Seal of Factory)" - below signature, italic, 8pt
```

### Step 9: Page Layout Extraction

**Measure:**
- Top margin (mm)
- Bottom margin (mm)
- Left margin (mm)
- Right margin (mm)
- Page numbering style and position

**Example:**
```
Top: 20mm
Bottom: 20mm
Left: 15mm
Right: 15mm
Page numbering: "Page X" - Bottom center - 8pt
```

### Step 10: Typography Extraction

**Extract:**
- Body font family
- Body font size
- Table font size
- Heading font sizes
- Line heights

**Example:**
```
Body: Times New Roman, 10pt, line-height 1.4
Table: Times New Roman, 9pt
Headings: Times New Roman, 14pt bold
```

---

## DOCUMENTATION FORMAT

For each form, create entry in `reference_structure_map.md`:

```markdown
### FORM_CODE - Form Name

**Reference PDF:** filename.pdf
**Extraction Date:** YYYY-MM-DD
**Extracted By:** [Name]

**FORM TITLE:** [Exact text]
**ACT REFERENCE:** [Exact text]
**RULE REFERENCE:** [Exact text]

**HEADER FORMATTING:**
- Title font: [size, weight, transform]
- Title alignment: [left/center/right]
- Act font: [size, style]
- Rule font: [size, style]
- Spacing: [measurements]

**ESTABLISHMENT BLOCK:**
- Layout: [description]
- Fields: [list in order]
- Label width: [measurement]

**TABLE STRUCTURE:**
| Column | Heading | Width | Align |
|--------|---------|-------|-------|
| 1 | [exact text] | [%] | [L/C/R] |
| 2 | [exact text] | [%] | [L/C/R] |
...

**TOTALS ROW:**
- Label: [exact text]
- Position: [description]
- Format: [styling]

**NIL DECLARATION:**
- Text: [exact text]
- Styling: [description]

**DECLARATION:**
- Text: [exact text word-for-word]

**SIGNATURE BLOCK:**
- Layout: [description]
- Left: [content]
- Right: [content]
- Seal: [position and text]

**PAGE LAYOUT:**
- Margins: [top/bottom/left/right in mm]
- Page numbering: [style and position]

**TYPOGRAPHY:**
- Body: [font, size, line-height]
- Table: [font, size]
- Headers: [font, size, weight]

**VERIFICATION:**
- [ ] Form title matches exactly
- [ ] Act reference matches exactly
- [ ] Rule reference matches exactly
- [ ] Column count correct
- [ ] Column order correct
- [ ] Column headings exact
- [ ] Totals format correct
- [ ] Declaration text exact
- [ ] Signature layout correct
```

---

## TEMPLATE POPULATION

After extraction, populate Blade template:

1. Open corresponding reference template in `resources/views/compliance/forms/reference/`
2. Replace all `{{-- TODO: ... --}}` comments with extracted values
3. Update section content with exact text
4. Adjust CSS in `custom_styles` section for form-specific styling
5. Test PDF generation
6. Compare generated PDF with reference PDF visually
7. Iterate until match is exact

---

## QUALITY CHECKLIST

Before marking form as complete:

- [ ] Generated PDF title matches reference PDF exactly
- [ ] Act and rule references match exactly
- [ ] Column count matches
- [ ] Column order matches (left to right)
- [ ] Column headings match exactly (no paraphrasing)
- [ ] Column widths proportional to reference
- [ ] Totals row format matches
- [ ] NIL declaration matches
- [ ] Declaration text matches word-for-word
- [ ] Signature block layout matches
- [ ] Page margins similar
- [ ] Font sizes similar
- [ ] Overall visual appearance matches

---

## TOOLS

**Recommended:**
- Adobe Acrobat Reader (text selection)
- PDF-XChange Viewer (measurement tools)
- Browser DevTools (CSS testing)
- Screenshot tool (visual comparison)

**Online Tools:**
- PDF to Text converters (for exact text extraction)
- PDF measurement tools

---

## NOTES

- Do NOT simplify or paraphrase any text
- Do NOT rename columns for "clarity"
- Do NOT rearrange layout for "better design"
- REPLICATE exactly as government PDF shows
- When in doubt, copy exact text from PDF
- Preserve all punctuation, capitalization, spacing

---

## COMPLETION TRACKING

Update `reference_structure_map.md` status for each form:
- ⏳ Awaiting reference PDF
- 📄 PDF received, extraction pending
- ✏️ Extraction in progress
- ✅ Extraction complete, template populated
- 🔍 Template under review
- ✔️ Verified and production-ready
