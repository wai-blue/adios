import React, { Component } from "react";
import axios from "axios";

import FormCardButton from './FormCardButton';
import Modal from './Modal';

interface CalendarProps {
  uid: string,
  loadDataController?: string,
  loadDataUrl?: string,
  cviciska: Array<any>,
  timy: Array<any>
}

interface CalendarState {
  rCnt: number,
  data?: Array<Array<Array<any>>>,
  idTim?: number,
  idCvicisko?: number,
  rok?: number,
  tyzden?: number,
  poradie?: number,
  calendarTitle: string,
  weekStart: Date,
  weekEnd: Date,
  weekNumber: number
}

const hoursRange = Array.from({ length: 17 }, (_, index) => index + 6);
const hodiny = ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne'];

const dni = ['Po', 'Ut', 'St', 'Št', 'Pi', 'So', 'Ne'];

export default class Calendar extends Component<CalendarProps> {
  state: CalendarState;
  url: string;

  title: JSX.Element;

  constructor(props: CalendarProps) {
    super(props);

    let now: Date = new Date();
    let lastMonday: Date = new Date(now.getTime() - (now.getDay() - 1) * 24 * 60 * 60 * 1000);
    let lastDayInWeek = new Date(lastMonday.getTime() + 6 * 24 * 60 * 60 * 1000);

    this.state = {
      rCnt: 0,
      idCvicisko: this.props.cviciska[0].id,
      idTim: this.props.timy[0].id,
      rok: 0,
      tyzden: 0,
      poradie: 0,
      calendarTitle: 'def',
      weekStart: lastMonday,
      weekEnd: lastDayInWeek,
      weekNumber: this.getWeekNumber(lastMonday)
    };

    console.log(this.state);

    this.url = props.loadDataController ?? props.loadDataUrl ?? '';
  }

  componentDidMount() {
    this.loadData();
  }

  loadData() {
    //@ts-ignore
    axios.get(_APP_URL + '/' + this.url, {
      params: {
        idTim: this.state.idTim,
        idCvicisko: this.state.idCvicisko,
        weekNumber: this.state.weekNumber,
        currentYear: this.state.weekStart.getFullYear()
      }
    }).then(({data}: any) => {
        this.setState({
          data: data.data
        }, () => {
          //@ts-ignore
          this.sortable(document.getElementById('adios-calendar-' + this.props.uid), function(item: any) {
            console.log(item);
          });
        })
    });
  }

  idTimOnChange(event: React.ChangeEvent<HTMLInputElement>) {
    this.setState({
      idTim: event.target.value
    }, () => {
      this.loadData();
    });
  }

  idCviciskoOnChange(event: React.ChangeEvent<HTMLInputElement>) {
    this.setState({
      idCvicisko: event.target.value
    }, () => {
      this.loadData();
    });
  }

  sortable(section: any, onUpdate: any) {
    var dragEl, nextEl, newPos, dragGhost;

    let oldPos = [...section.children].map(item => {
      if (item.id) {
        item.draggable = true;
        let pos = document.getElementById(item.id)?.getBoundingClientRect();

        return pos;
      }
    });
  
    function _onDragOver(e:  any) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';

      $('.rezervacka').removeClass('drag-over');
      var target = e.target;

      if (
        target
        && target !== dragEl
        && target.nodeName == 'DIV'
        && target.classList.contains('rezervacka')
        && $(target).data('den') == $(dragEl).data('den')
      ) {
          $(target).addClass('drag-over');
          //  getBoundinClientRect contains location-info about the element (relative to the viewport)
          var targetPos = target.getBoundingClientRect();

          // checking that dragEl is dragged over half the target y-axis or x-axis. (therefor the .5)
          var next =
            (e.clientY - targetPos.top) / (targetPos.bottom - targetPos.top) > .5
            || (e.clientX - targetPos.left) / (targetPos.right - targetPos.left) > .5
          ;
          var next =
            (e.clientX - targetPos.left) / (targetPos.right - targetPos.left) > .5
          ;

          //{# section.insertBefore(dragEl, next && target.nextSibling || target); #}
          section.insertBefore(dragEl, target.nextSibling);
      } else {
        e.stopPropagation();
      }
    }
    
    function _onDragEnd(evt: any){
      evt.preventDefault();
      newPos = [...section.children].map(child => {
        if (child.id) {
          let pos = document.getElementById(child.id)?.getBoundingClientRect();

          return pos;
        }
      });
      console.log(newPos);
      dragEl.classList.remove('ghost');
      section.removeEventListener('dragover', _onDragOver, false);
      section.removeEventListener('dragend', _onDragEnd, false);

      nextEl !== dragEl.nextSibling ? onUpdate(dragEl) : false;
    }
      
    section.addEventListener('dragstart', function(e: any){
      dragEl = e.target; 
      nextEl = dragEl.nextSibling;

      let dragGhostInner = dragEl.cloneNode(true);

      //{# dragGhost = document.createElement('div');
      //dragGhost.classList.add('hidden-drag-ghost');
      //dragGhost.appendChild(dragGhostInner);
      //document.body.appendChild(dragGhost);
      //e.dataTransfer.setDragImage(dragGhost, 0, 0); #}

      e.dataTransfer.effectAllowed = 'move';
      e.dataTransfer.setData('Text', dragEl.textContent);

      section.addEventListener('dragover', _onDragOver, false);
      section.addEventListener('dragend', _onDragEnd, false);

      setTimeout(function (){
        dragEl.classList.add('ghost');
      }, 0)

    });
  }

  _addOpacity(color: string, opacity: string): string {
    return color + opacity;
  }

  _renderCalendar(): JSX.Element {
    if (!this.state.data) return <p>Loading</p>;

    return (
      <>
        {Array.from({ length: 7 }, (_, d) => (
          <React.Fragment key={d}>
            {(() => {
              let hh = 6;
              let mm = 0;

              if (!this.state.data) return;

              return (
                <React.Fragment>
                  <div className="header">30.10.</div>
                  <div className="header">{dni[d]}</div>

                  {this.state.data[d]
                    ? (this.state.data[d].map((r: any) => {
                      if (r[1] === 0 && r[2] === '' && r[3] === '') {
                        return Array.from({ length: r[0] / 15 }, (_, v) => {
                          mm += 15;
                          if (mm >= 60) {
                            mm -= 60;
                            hh += 1;
                          }

                          return (
                            <div
                              id={`rezervacka-${this.state.rCnt++}`}
                              key={this.state.rCnt}
                              className={
                                "rezervacka volne "
                                + (this.state.rCnt % 4 == 0 ? "cela-hodina" : "")
                                + " " + (r[7] != this.state.idTim ? "readonly" : "")
                              }
                              data-den={d}
                              onClick={() =>
                                //@ts-ignore
                                ADIOS.modalToggle(this.props.uid)
                                //@ts-ignore
                                //ADIOS.modal(
                                //  'rozpis/form-rezervacia-pridat',
                                //  { hh, mm },
                                //  { title: `Rezervácia cvičiska na čas: ${hh}:${mm}` }
                                //)
                              }
                            ></div>
                          );
                        });
                      } else {
                        const span = (r[0] + r[1]) / 15;
                        const gridColumn = `span ${span}`;

                        this.state.rCnt += span;

                        return (
                          <div
                            id={`rezervacka-${this.state.rCnt}`}
                            key={this.state.rCnt}
                            className={
                              "rezervacka"
                              + " " + (r[7] != this.state.idTim ? "readonly" : "")
                            }
                            data-den={d}
                            style={{ gridColumn }}
                          >
                            <div className="rezervacka-inner" style={{ flex: r[0] / 15 }}>
                              {Array.isArray(r[2]) ? (
                                r[2].map((rr) => (
                                  <div
                                    key={rr[1]}
                                    className="cast-cviciska"
                                    style={{ background: this._addOpacity(rr[0], '60') }}
                                    onClick={() => {
                                      //@ts-ignore
                                      ADIOS.modalToggle(this.props.uid);
                                    }}
                                  >
                                    {rr[1]}
                                  </div>
                                ))
                              ) : (
                                <div
                                  className="cast-cviciska"
                                  style={{ background: this._addOpacity(r[2], '60') }}
                                  //onClick={() => ADIOS.modal('rozpis/treningy/form', { hh, mm })}
                                  onClick={() => {
                                      //@ts-ignore
                                      ADIOS.modalToggle(this.props.uid);
                                  }}
                                >
                                  <div className="cas">{`${hh}:${mm}`}</div>
                                  <div className="nazov">{r[3]}</div>
                                  <div className="trener">{r[4]}</div>
                                </div>
                              )}
                            </div>
                            <div className="rolba" style={{ flex: r[1] / 15 }}></div>
                          </div>
                        );
                      }
                    })
                  ) : ''}
                </React.Fragment>
              );
            })()}
          </React.Fragment>
        ))}
      </>
    );
  }

  getWeekNumber(week: Date) {
    let newDate = new Date(week);
    newDate.setDate(newDate.getDate() + 4 - (newDate.getDay() || 7));

    let year = newDate.getFullYear();
    let daysDiff = Math.floor((newDate.getTime() - new Date(year, 0, 4).getTime()) / (24 * 60 * 60 * 1000));

    return Math.ceil((daysDiff + 1) / 7);
  }

  calculateWeeks(type: string = 'next') {
    let weeks: Object = {};

    let weekStart = new Date(this.state.weekStart.getTime() + (type == 'next' ? 7 : -7) * 24 * 60 * 60 * 1000);

    weeks = {
      weekStart: weekStart,
      weekEnd: new Date(this.state.weekEnd.getTime() + (type == 'next' ? 7 : -7) * 24 * 60 * 60 * 1000),
      weekNumber: this.getWeekNumber(weekStart)
    }

    this.setState({...weeks}, () => {
        this.loadData();
    })
  }

  closeAndLoadData(modalUid: string) {
    this.loadData();
    //@ts-ignore
    ADIOS.modalToggle(this.props.uid + '-' + modalUid);
    //@ts-ignore
    ADIOS.modalToggle(this.props.uid);
  }

  render() {
    return (
      <>
        <Modal
          title="Rezervácia cvičiska"
          uid={this.props.uid}
        >
          <FormCardButton
            uid={this.props.uid + '-trening'}
            text="Tréning"
            css="btn-primary m-1"
            icon="fas fa-running"
            form={{
              uid: this.props.uid + '-trening',
              model: "App/Widgets/Rozpis/Models/Trening",
              onSaveCallback: () => this.closeAndLoadData('trening'),
              onDeleteCallback: () => this.closeAndLoadData('trening')
            }}
          ></FormCardButton>

          <FormCardButton
            uid={this.props.uid + '-zapas'}
            text="Zápas"
            css="btn-light"
            icon="fas fa-people-arrows m-1"
            form={{
              uid: this.props.uid + '-zapas',
              model: "App/Widgets/Rozpis/Models/Zapas",
              onSaveCallback: () => this.closeAndLoadData('zapas'),
              onDeleteCallback: () => this.closeAndLoadData('zapas')
            }}
          ></FormCardButton>

          <FormCardButton
            uid={this.props.uid + '-komercia'}
            text="Komercia"
            css="btn-light m-1"
            icon="fas fa-euro-sign"
            form={{
              uid: this.props.uid + '-komercia',
              model: "App/Widgets/Rozpis/Models/Komercia",
              onSaveCallback: () => this.closeAndLoadData('komercia'),
              onDeleteCallback: () => this.closeAndLoadData('komercia')
            }}
          ></FormCardButton>
        </Modal>

        <div className="row mb-2">
          <div className="col-lg-6 pl-0">
            <div className="d-flex flex-row align-items-center">
              <select
                className="form-control rounded-sm"
                style={{maxWidth: '250px'}}
                onChange={(event: any) => this.idCviciskoOnChange(event)}
                value={this.state.idCvicisko}
              >
                {this.props.cviciska.map((cvicisko: any) => (
                  <option
                    key={cvicisko.id}
                    value={cvicisko.id}
                  >{cvicisko.nazov}</option>
                ))}
              </select>

              <span className="ml-2 mr-2">&raquo;</span>

              <select
                className="form-control rounded-sm"
                style={{maxWidth: '250px'}}
                onChange={(event: any) => this.idTimOnChange(event)}
                value={this.state.idTim}
              >
                {this.props.timy.map((tim: any) => (
                  <option
                    key={tim.id}
                    value={tim.id}
                  >{tim.nazov}</option>
                ))}
              </select>
            </div>
          </div>
          <div className="col-lg-6 pr-0">
            <div className="d-flex flex-row justify-content-end align-items-center">
              <a href="#" onClick={() => this.calculateWeeks('previous')} className="btn btn-primary">«</a>
              <div className="text-primary text-center" style={{margin: "0 0.5em", width: "200px"}}>
                { this.state.weekStart.getDate() }.{ this.state.weekStart.getMonth() + 1 }.{ this.state.weekStart.getFullYear() }
                &nbsp;-&nbsp;
                { this.state.weekEnd.getDate() }.{ this.state.weekEnd.getMonth() + 1 }.{ this.state.weekStart.getFullYear() }
              </div>
              <a href="#" onClick={() => this.calculateWeeks()} className="btn btn-primary">»</a>
            </div>
          </div>
        </div>

        <div id={'adios-calendar-' + this.props.uid} className="rezervacny-kalendar rounded-sm">
          <div className="header" style={{ gridRow: 'span 2' }}>Dátum</div>
          <div className="header">Hodina</div>

          {hoursRange.map((h) => (
            <div key={h} className="header hodiny">
              {h} - {h + 1}
            </div>
          ))}

          <div className="header">Deň</div>
          {this._renderCalendar()}
        </div>
        <div>
          idCvicisko = {this.state.idCvicisko};
          idTim = {this.state.idTim};
          rok = {this.state.rok};
          tyzden = {this.state.tyzden};
          poradie = {this.state.poradie};
        </div>
      </>
    );
  }
}

